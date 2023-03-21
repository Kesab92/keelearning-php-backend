<?php

namespace App\Http\Controllers\BackendApi;

use App\Exceptions\Settings\DuplicationException;
use App\Http\Controllers\Controller;
use App\Mail\Mailer;
use App\Mail\SMTPSettingsMail;
use App\Models\AccessLog;
use App\Models\App;
use App\Models\AppProfile;
use App\Models\AppProfileHomeComponent;
use App\Models\AppProfileSetting;
use App\Models\FrontendTranslation;
use App\Models\User;
use App\Push\Deepstream;
use App\Services\AccessLogEngine;
use App\Services\AccessLogMeta\AccessLogAppProfileChanged;
use App\Services\AccessLogMeta\AccessLogUserUpdate;
use App\Services\AppProfileHomeComponentsEngine;
use App\Services\AppProfileSettings;
use App\Services\AppSettings;
use App\Services\ImageUploader;
use App\Services\StatsEngine;
use App\Services\UserEngine;
use Auth;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Log;
use Mail;
use Response;

class SettingsController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:,settings-edit');
    }

    public function getAppSettings(AppSettings $settings)
    {
        $publicSettings = $settings->publicSettings();
        $allowedSettings = $settings->allowedSettings();
        $data = [];
        foreach ($allowedSettings as $setting) {
            $value = $settings->getValue($setting);
            if ($value === '1') {
                $value = true;
            }
            if ($value === '0') {
                $value = false;
            }
            $data[$setting] = [
                'value' => $value,
                'superadmin' => ! in_array($setting, $publicSettings),
            ];
        }

        return Response::json($data);
    }

    public function getAvailableModules(AppSettings $settings)
    {
        $modules = array_keys(AppSettings::$modules);
        $data = [];
        foreach ($modules as $setting) {
            $value = $settings->getValue($setting);
            if ($value === '1') {
                $value = true;
            }
            if ($value === '0') {
                $value = false;
            }
            if($value) {
                $data[] = $setting;
            }
        }

        return Response::json($data);
    }

    public function isCandy(AppSettings $appSettings)
    {
        return Response::json([
            'isCandy' => $appSettings->getValue('has_candy_frontend') == "1"
        ]);
    }

    public function updateAppSettings(Request $request, AppSettings $settings)
    {
        $allowedSettings = $settings->allowedSettings();
        $newSettings = $request->input('settings');
        foreach ($newSettings as $newSetting) {
            if (! in_array($newSetting['key'], $allowedSettings)) {
                app()->abort(403);
            }
            $value = $newSetting['value'];
            if ($value === true) {
                $value = 1;
            }
            if ($newSetting['key'] === 'smtp_password' && $value) {
                $value = encrypt($value);
            }
            if ($newSetting['key'] === 'smtp_host' && ! $value) {
                $settings->setValue('smtp_password', '');
            }

            $settings->setValue($newSetting['key'], $value);
        }

        return Response::json([]);
    }

    public function getAppProfileSettings($appProfileId)
    {
        $appProfileSettings = new AppProfileSettings((int) $appProfileId);
        $allowedSettings = $appProfileSettings->allowedSettings();
        $allowedSettingKeys = array_keys($allowedSettings);
        $data = [];
        foreach ($allowedSettingKeys as $setting) {
            $default = $allowedSettings[$setting]['default'];
            $originalValue = $appProfileSettings->getValue($setting, true);
            if ($allowedSettings[$setting]['type'] !== 'number') {
                if ($originalValue === '1') {
                    $originalValue = true;
                }
                if ($originalValue === '0') {
                    $originalValue = false;
                }
            }
            if ($originalValue === null) {
                $value = $default;
            } else {
                $value = $originalValue;
            }
            if($setting === 'smtp_password') {
                $value = '';
                $originalValue = '';
            }
            $data[$setting] = [
                'value' => $value,
                'original_value' => $originalValue,
                'superadmin' => $allowedSettings[$setting]['access'] === 'superadmin',
                'default' => $default,
            ];
        }

        return Response::json($data);
    }

    public function updateAppProfileSettings($appProfileId, Request $request, AccessLogEngine $accessLogEngine)
    {
        $appProfile = AppProfile::findOrFail($appProfileId);

        if($appProfile->app_id !== appId()) {
            abort(403);
        }

        $response = [];
        $appProfileSettings = new AppProfileSettings((int) $appProfileId);
        $allowedSettings = array_keys($appProfileSettings->allowedSettings());

        $newSettings = $request->input('settings');
        foreach ($newSettings as $newSetting) {
            $key = $newSetting['key'];
            if (! in_array($key, $allowedSettings)) {
                app()->abort(403, 'Not allowed to edit ' . $key);
            }
            $value = $newSetting['value'];
            if ($value === null) {
                $value = '';
            }
            if ($value === true) {
                $value = 1;
            }
            if ($value === false) {
                $value = 0;
            }

            if ($key === 'smtp_password') {
                if (isset($value) && strlen($value) > 0) {
                    $value = encrypt($value);
                }
            }

            if ($key === 'smtp_password' && !$value) {
                $appProfileSettings->setValue('smtp_password', '');
                $accessLogEngine->log(AccessLog::ACTION_UPDATE_APP_PROFILE_SETTING, new AccessLogAppProfileChanged($appProfileId, 'smtp_password', ''));
            }

            if ($key === 'days_before_user_deletion') {
                $this->notifyExpiringUsers($appProfileSettings, $appProfile, $value);
            }

            try {
                $appProfileSettings->setValue($key, $value);
            } catch (DuplicationException $e) {
                $response['error'] = $value.' ist schon vergeben';
            }

            $this->dispatchLiveUpdate();

            $accessLogEngine->log(AccessLog::ACTION_UPDATE_APP_PROFILE_SETTING, new AccessLogAppProfileChanged($appProfileId, $key, $value));
        }
        if(!empty($response['error'])) {
            return Response::json($response, 400);
        } else {
            return Response::json($response);
        }
    }

    /**
     * Tells the clients to update their app config.
     */
    private function dispatchLiveUpdate()
    {
        Deepstream::getClient()->emitEvent('appProfile/' . appId() . '/update');
    }

    public function getDummyUser()
    {
        if (! Auth::user()->isSuperAdmin()) {
            app()->abort(403);
        }
        $dummyUser = User::where('app_id', appId())->where('is_dummy', true)->first();
        $data = [];
        if ($dummyUser) {
            $data = $dummyUser->only('id', 'username', 'email');
        }

        return Response::json($data);
    }

    public function updateDummyUser(Request $request, AccessLogEngine $accessLogEngine)
    {
        if (! Auth::user()->isSuperAdmin()) {
            app()->abort(403);
        }

        /** @var User $user */
        $user = User::find($request->input('dummyUserId'));

        // Check user exists
        if (! $user) {
            return Response::json([
                'error' => 'Konnte diesen Benutzer nicht finden',
            ]);
        }

        // Check the access rights
        if ($user->app_id != appId()) {
            return Response::json([
                'error' => $user->username.' gehört nicht zu dieser App.',
            ]);
        }

        if ($user->is_admin || $user->deleted_at) {
            return Response::json([
                'error' => $user->username.' kann nicht als Platzhalter Nutzer verwendet werden, da er entweder ein Admin, oder gelöscht ist.',
            ]);
        }

        User::where('app_id', $user->app_id)
            ->where('is_dummy', true)
            ->update([
                'is_dummy' => false,
            ]);

        $user->is_dummy = true;
        $accessLogEngine->log(AccessLog::ACTION_USER_UPDATE, new AccessLogUserUpdate($user));
        $user->save();

        return Response::json([]);
    }

    public function getAppProfiles()
    {
        /** @var App $app */
        $app = App::find(appId());
        $profiles = AppProfile
            ::where('app_id', $app->id)
            ->with('tags')
            ->select('id', 'name', 'is_default')
            ->get();

        $profiles = collect($profiles->toArray())->transform(function($profile) {
            $profile['tags'] = array_map(function($tag) {
                return [
                    'id' => $tag['id'],
                    'label' => $tag['label'],
                ];
            }, $profile['tags']);
            return $profile;
        });

        return Response::json($profiles);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function testSmtpSettings(Request $request)
    {
        $this->validate($request, [
            'email' => 'required',
            'smtp_host' => 'required',
            'smtp_port' => 'required',
            'smtp_email' => 'required',
            'smtp_username' => 'required',
            'smtp_password' => 'required',
            'smtp_encryption' => 'required',
        ]);

        try {
            config([
                'mail.driver'     => 'smtp',
                'mail.host'       => $request->input('smtp_host'),
                'mail.port'       => $request->input('smtp_port'),
                'mail.encryption' => $request->input('smtp_encryption'),
                'mail.username'   => $request->input('smtp_username'),
                'mail.password'   => $request->input('smtp_password'),
                'mail.from.address' => $request->input('smtp_email'),
                'mail.stream.ssl.allow_self_signed' => true,
                'mail.stream.ssl.verify_peer' => false,
                'mail.stream.ssl.verify_peer_name' => false,
            ]);

            // Reload the mail config
            Mail::purge('smtp');
            Mail::alwaysFrom($request->input('smtp_email'), env('MAIL_COURIER_NAME'));
            Mail::to($request->input('email'))
                ->send(new SMTPSettingsMail());

            return Response::json([
                'success' => true,
            ]);
        } catch (\Exception $e) {
            Log::error($e->__toString());

            return Response::json([
                'success' => false,
            ]);
        }
    }

    public function updateAppProfileImage($appProfileId, $setting, Request $request, ImageUploader $imageUploader, AccessLogEngine $accessLogEngine)
    {
        if(AppProfile::findOrFail($appProfileId)->app_id !== appId()) {
            abort(403);
        }

        $appProfileSettings = new AppProfileSettings((int) $appProfileId);
        $allowedSettings = array_keys($appProfileSettings->allowedSettings());

        if (! in_array($setting, $allowedSettings)) {
            app()->abort(403);
        }
        $file = $request->file('file');
        if (! $imageUploader->validate($file)) {
            app()->abort(403);
        }

        if (! $imagePath = $imageUploader->upload($file, 'uploads/'.Str::slug($setting))) {
            app()->abort(400);
        }
        $imagePath = formatAssetURL($imagePath, '3.0.0');

        $appProfileSettings->setValue($setting, $imagePath);

        $this->dispatchLiveUpdate();

        $accessLogEngine->log(AccessLog::ACTION_UPDATE_APP_PROFILE_SETTING, new AccessLogAppProfileChanged($appProfileId, $setting, $imagePath));

        return Response::json([
            'image' => $imagePath,
        ]);
    }

    public function getCustomerInfo(StatsEngine $statsEngine)
    {
        if (! isSuperAdmin()) {
            app()->abort(403);
        }
        /** @var App $app */
        $app = App::find(appId());

        return Response::json([
            'support_phone_number' => $app->support_phone_number,
            'internal_notes' => $app->internal_notes,
            'user_licences' => $app->user_licences,
            'active_users' => $statsEngine->getRecentlyActiveUserCount(),
            'running_games' => $statsEngine->runningGames(),
            'defaultLanguage' => $app->getLanguage(),
            'languages' => $app->getLanguages(),
        ]);
    }

    public function updateCustomerInfo(Request $request)
    {
        if (! isSuperAdmin()) {
            app()->abort(403);
        }
        /** @var App $app */
        $app = App::find(appId());
        $app->support_phone_number = $request->input('support_phone_number');
        $app->internal_notes = $request->input('internal_notes');
        $app->user_licences = $request->input('user_licences');
        $app->save();

        $appSettings = new AppSettings($app->id);
        $newLanguages = $request->input('languages');
        $defaultLanguage = App::getLanguageById($app->id);
        if(!in_array($defaultLanguage, $newLanguages)) {
            $newLanguages[] = $defaultLanguage;
        }
        $appSettings->setValue('languages', json_encode($newLanguages));

        return Response::json([], 204);
    }

    public function getTemplateInheritances()
    {
        if (! isSuperAdmin()) {
            app()->abort(403);
        }
        /** @var App $app */
        $app = App::find(appId());

        $apps = App::select('id')
            ->with('profiles.settings')
            ->where('id', '!=', $app->id)
            ->get()
            ->transform(function(App $app) {
                return [
                    'id' => $app->id,
                    'app_name' => $app->app_name,
                ];
            });

        return Response::json([
            'templateInheritanceChildren' => $app->templateInheritanceChildren()->pluck('child_id'),
            'apps' => $apps,
        ]);
    }

    public function updateTemplateInheritances(Request $request)
    {
        if (! isSuperAdmin()) {
            app()->abort(403);
        }
        /** @var App $app */
        $app = App::find(appId());

        $app->templateInheritanceChildren()->sync($request->input('children'));

        return Response::json([], 204);
    }

    /**
     * Gets translations for the app profile
     *
     * @param $appProfileId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getAppTranslations($appProfileId)
    {
        if (! isSuperAdmin()) {
            app()->abort(403);
        }

        $appProfile = AppProfile::findOrFail($appProfileId);

        if($appProfile->app_id !== appId()) {
            abort(403);
        }

        $translations = FrontendTranslation
            ::where('app_profile_id', $appProfile->id)
            ->where('language', language())
            ->select(['id', 'content', 'key'])
            ->get();

        return Response::json([
            'translations' => $translations
        ]);
    }

    /**
     * Saves translations for the app profile
     *
     * @param $appProfileId
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function updateAppTranslations($appProfileId, Request $request)
    {
        if (! isSuperAdmin()) {
            app()->abort(403);
        }

        $appProfile = AppProfile::findOrFail($appProfileId);

        if($appProfile->app_id !== appId()) {
            abort(403);
        }

        DB::transaction(function() use ($appProfile, $request) {
            $newTranslations = collect($request->input('translations'));

            // Remove deleted translations
            FrontendTranslation
                ::where('app_profile_id', $appProfile->id)
                ->where('language', language())
                ->whereNotIn('id', $newTranslations->pluck('id'))
                ->delete();

            // Update translations / create new translations
            $dbTranslations = FrontendTranslation
                ::where('app_profile_id', $appProfile->id)
                ->where('language', language())
                ->whereIn('id', $newTranslations->pluck('id'))
                ->get()
                ->keyBy('id');
            foreach($newTranslations as $translation) {
                $key = utrim($translation['key']);
                if(!$key) {
                    continue;
                }
                if($translation['id'] > 0) {
                    $dbTranslation = $dbTranslations->get($translation['id']);
                    if(!$dbTranslation) {
                        throw new \Exception('Die Übersetzung mit id ' . $translation['id'] . ' gibt es nicht mehr.');
                    }
                } else {
                    $dbTranslation = new FrontendTranslation();
                    $dbTranslation->app_profile_id = $appProfile->id;
                    $dbTranslation->language = language();
                }
                $dbTranslation->key = $translation['key'];
                $dbTranslation->content = $translation['content'];
                $dbTranslation->save();
            }
        });


        return Response::json([], 204);
    }

    public function getHomeComponents($appProfileId)
    {
        $appProfile = AppProfile::where('id', $appProfileId)
            ->where('app_id', appId())
            ->firstOrFail();
        $homeComponentsEngine = new AppProfileHomeComponentsEngine();
        return Response::json([
            'blueprints' => AppProfileHomeComponent::BLUEPRINTS,
            'components' => $homeComponentsEngine->getHomeComponents($appProfile),
        ]);
    }

    public function updateHomeComponents($appProfileId, Request $request)
    {
        $appProfile = AppProfile::where('id', $appProfileId)
            ->where('app_id', appId())
            ->firstOrFail();
        $homeComponentsEngine = new AppProfileHomeComponentsEngine();
        $success = $homeComponentsEngine->saveHomeComponents($appProfile, $request->input('components'));
        if (!$success) {
            app()->abort(400);
        }
        $this->dispatchLiveUpdate();
        return Response::json([], 204);
    }

    /**
     * Notifies expiring users about deletion
     * @param AppProfileSettings $appProfileSettings
     * @param AppProfile $appProfile
     * @param int|string $settingValue
     * @return void
     */
    private function notifyExpiringUsers(AppProfileSettings $appProfileSettings, AppProfile $appProfile, $settingValue) {
        $oldSettingValue = (int)$appProfileSettings->getValue('days_before_user_deletion');

        // If the value isn't changed, skip notification sending
        if ($oldSettingValue == $settingValue) {
            return;
        }

        $oldDaysBefore = $oldSettingValue ?: AppProfileSetting::DAYS_BEFORE_USER_DELETION;
        $newDaysBefore = (int)$settingValue ?: AppProfileSetting::DAYS_BEFORE_USER_DELETION;

        $userEngine = app(UserEngine::class);
        $mailer = app(Mailer::class);
        $today = Carbon::today();
        $timeframeStartDate = Carbon::today()->addDays($oldDaysBefore);
        $timeframeEndDate = Carbon::today()->addDays($newDaysBefore);

        $userEngine
            ->getUsersWithCombinedExpiresAtQuery($appProfile->app_id)
            ->with('tags')
            ->chunk(1000, function ($users) use ($timeframeStartDate, $timeframeEndDate, $today, $mailer, $appProfile) {
                foreach ($users as $user) {
                    $userAppProfile = $user->getAppProfile();
                    $userExpiresAt = Carbon::createFromFormat('Y-m-d', $user->expires_at_combined)->startOfDay();
                    $dayCount = $today->diffInDays($userExpiresAt);

                    if ($userAppProfile->id == $appProfile->id && $userExpiresAt->gt($timeframeStartDate) && $userExpiresAt->lte($timeframeEndDate)) {
                        $mailer->sendExpirationReminder($user, $dayCount);
                    }
                }
            });
    }
}
