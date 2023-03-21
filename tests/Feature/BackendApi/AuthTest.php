<?php

namespace Tests\Feature\BackendApi;


use App\Models\App;
use App\Models\User;
use Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    protected User $admin;
    const ADMIN_EMAIL = 'admin@example.org';
    const ADMIN_PASSWORD = 'secret';

    public function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()
            ->active()
            ->admin()
            ->create([
                'app_id' => $this->quizApp->id,
                'email' => self::ADMIN_EMAIL,
                'password' => Hash::make(self::ADMIN_PASSWORD),
            ]);
    }

    public function test_login_shows_apps()
    {
        $secondQuizApp = App::factory()->create([
            'id' => 2,
            'app_hosted_at' => 'http://127.0.0.1',
        ]);
        $secondAdminAccount = User::factory()
            ->active()
            ->admin()
            ->create([
                'app_id' => $secondQuizApp->id,
                'is_admin' => true,
                'email' => self::ADMIN_EMAIL,
            ]);

        $quizAppWithoutAdmin = App::factory()->create([
            'id' => 3,
            'app_hosted_at' => 'http://127.0.0.1',
        ]);
        User::factory()
            ->active()
            ->create([
                'app_id' => $quizAppWithoutAdmin->id,
                'email' => self::ADMIN_EMAIL,
            ]);

        $quizAppWithInactiveAdmin = App::factory()->create([
            'id' => 4,
            'app_hosted_at' => 'http://127.0.0.1',
        ]);
        User::factory()
            ->admin()
            ->create([
                'app_id' => $quizAppWithInactiveAdmin->id,
                'active' => false,
                'email' => self::ADMIN_EMAIL,
            ]);

        $expectedResponse = [];
        $expectedResponse[$this->quizApp->id] = $this->quizApp->name;
        $expectedResponse[$secondQuizApp->id] = $secondQuizApp->name;

        $this->json('GET', '/login/apps?email=' . urlencode(self::ADMIN_EMAIL))
            ->assertStatus(200)
            ->assertExactJson($expectedResponse);
    }

    public function test_login_works()
    {
        $this->json('POST', '/login', [
                'email' => self::ADMIN_EMAIL,
                'password' => self::ADMIN_PASSWORD,
                'appid' => $this->quizAppId,
            ])
            ->assertRedirect('/');
    }

    public function test_login_without_app_id_fails()
    {
        $this->json('POST', '/login', [
                'email' => self::ADMIN_EMAIL,
                'password' => self::ADMIN_PASSWORD,
            ])
            ->assertRedirect('/login');
    }

    public function test_login_to_wrong_app_id_fails()
    {
        $this->json('POST', '/login', [
                'email' => self::ADMIN_EMAIL,
                'password' => self::ADMIN_PASSWORD,
                'appid' => 20,
            ])
            ->assertRedirect('/login');
    }

    public function test_login_as_inactive_admin_fails()
    {
        User::factory()
            ->admin()
            ->create([
                'app_id' => $this->quizApp->id,
                'active' => false,
                'email' => 'inactiveadmin@example.org',
                'password' => Hash::make(self::ADMIN_PASSWORD),
            ]);
        $this->json('POST', '/login', [
                'email' => 'inactiveadmin@example.org',
                'password' => self::ADMIN_PASSWORD,
                'appid' => $this->quizApp->id,
            ])
            ->assertRedirect('/login');
    }

    public function test_login_with_wrong_credentials_fails()
    {
        $this->json('POST', '/login', [
                'email' => 'foobar@example.org',
                'password' => self::ADMIN_PASSWORD,
                'appid' => $this->quizAppId,
            ])
            ->assertRedirect('/login');
        $this->json('POST', '/login', [
                'email' => self::ADMIN_EMAIL,
                'password' => 'foobar',
                'appid' => $this->quizAppId,
            ])
            ->assertRedirect('/login');
    }

    public function test_login_as_non_admin_fails()
    {
        $user = User::factory()->active()->create([
            'app_id' => $this->quizApp->id,
            'is_admin' => false,
            'email' => 'user@example.org',
            'password' => Hash::make(self::ADMIN_PASSWORD),
        ]);
        $this->json('POST', '/login', [
                'email' => 'user@example.org',
                'password' => self::ADMIN_PASSWORD,
                'appid' => $this->quizAppId,
            ])
            ->assertRedirect('/login');
    }
}
