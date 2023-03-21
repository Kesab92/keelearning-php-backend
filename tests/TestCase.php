<?php

namespace Tests;

use App\Models\App;
use App\Models\AppProfile;
use App\Models\MailTemplate;
use App\Models\MailTemplateTranslation;
use App\Models\User;
use App\Models\UserRole;
use App\Services\AppProfileSettings;
use App\Services\AppSettings;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;
use JWTAuth;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    // use RefreshDatabase;
    use WithFaker;
    use DatabaseTransactions;

    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://qa.test';
    protected $refreshDatabase = false; // manually re-migrate the whole database after each test

    protected int $quizAppId = App::ID_BPMO; // We're using this app, because it has meta fields and no hardcoded exceptions
    protected App $quizApp;
    private User $mainAdmin;
    private User $permissionslessAdmin;

    public function setUp(): void
    {
        parent::setUp();
        $this->setupDatabase();
        $this->clearStaticCaches();
        $this->withHeader('X-API-VERSION', '3.1.0');
    }

    protected function setupDatabase(): void
    {
        if ($this->refreshDatabase) {
            // when using Illuminate\Foundation\Testing\RefreshDatabase
            // the database refresh is sometimes triggered in the middle of
            // the test, if too many DB queries are made
            $this->artisan('migrate:fresh');
        }

        $this->quizApp = $this->createQuizApp($this->quizAppId);
    }

    protected function createQuizApp(int $quizAppId): App
    {
        $app = App::factory()
            ->create([
                'id' => $quizAppId,
                'app_hosted_at' => $this->baseUrl,
            ]);

        // TODO: Find a proper way to setup email
        $mailTemplate = new MailTemplate();
        $mailTemplate->app_id = $app->id;
        $mailTemplate->type = 'AppInvitation';
        $mailTemplate->save();
        $mailTemplateTranslation = new MailTemplateTranslation();
        $mailTemplateTranslation->mail_template_id = $mailTemplate->id;
        $mailTemplateTranslation->save();

        return $app;
    }

    protected function clearStaticCaches(): void
    {
        App::clearStaticCache();
        AppProfile::clearStaticCache();
        AppSettings::clearStaticCache();
        User::clearStaticCache();
    }

    protected function getMainAdmin(): User
    {
        if (!isset($this->mainAdmin)) {
            $this->mainAdmin = User::factory()
                ->active()
                ->admin()
                ->create([
                    'app_id' => $this->quizApp->id,
                    'user_role_id' => UserRole::where('app_id', $this->quizApp->id)->where('is_main_admin', 1)->first(),
                ]);
        }
        return $this->mainAdmin;
    }

    protected function getPermissionslessAdmin(): User
    {
        if (!isset($this->permissionslessAdmin)) {
            $this->permissionslessAdmin = User::factory()
                ->active()
                ->admin()
                ->create([
                    'app_id' => $this->quizApp->id,
                    'user_role_id' => UserRole::where('app_id', $this->quizApp->id)->where('is_main_admin', 0)->first(),
                ]);
        }
        return $this->permissionslessAdmin;
    }

    protected function activateAllAppModules(?App $app = null): void
    {
        if (!$app) {
            $app = $this->quizApp;
        }

        $appSettings = new AppSettings($app->id);
        foreach (AppSettings::$modules as $module => $description) {
            $appSettings->setValue($module, 1);
        }

        $profileSettings = $app->getDefaultAppProfile();
        $appProfileModules = collect(AppProfileSettings::$settings)
            ->filter(function ($settings) {
                return $settings['type'] == 'module';
            })
            ->keys();
        foreach ($appProfileModules as $module) {
            $appProfileSettings = new AppProfileSettings($profileSettings->id);
            $appProfileSettings->setValue($module, 1);
        }
    }

    /**
     * Logs the given user in
     * Future API calls will be made on behalf of this user.
     *
     * @param $user_id
     */
    protected function setAPIUser($user_id): void
    {
        $user = User::find($user_id);
        if (!$user) {
            dd('Couldn\'t find user with id: ' . $user_id);
        }
        $token = JWTAuth::fromUser($user);
        JWTAuth::setToken($token);
        $this->setAPIToken($token);
    }

    protected function setBackendAPIUser(User $user): void
    {
        $this->actingAs($user);
    }

    protected function setAPIToken(?string $token): void
    {
        $this->withHeader('X-QUIZAPP-AUTHORIZATION', 'Bearer ' . $token);
    }

    protected function isDate($allowNull = false, Carbon $exactDate = null): callable
    {
        return function($value) use ($allowNull, $exactDate) {
            if($allowNull && is_null($value)) {
                return true;
            }
            if($exactDate) {
                return $value === $exactDate->toIso8601ZuluString();
            }
            return Carbon::canBeCreatedFromFormat($value, 'Y-m-d\TH:i:s\Z');
        };
    }

    protected function backendAPIUrl(string $path): string
    {
        return '/backend/api/v1' . $path;
    }
}
