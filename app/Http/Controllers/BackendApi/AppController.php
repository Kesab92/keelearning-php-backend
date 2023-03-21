<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Services\AppProfileSettings;
use App\Services\AppSettings;
use App\Services\StatsEngine;
use Auth;
use Illuminate\Http\JsonResponse;
use Response;

class AppController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth.superadmin')->only('getDetailsOfAllApps');
    }

    public function getConfiguration(): JsonResponse
    {
        $app = App::find(appId());
        $languages = [];
        foreach($app->getLanguages() as $language) {
            $languages[$language] = __('general.lang_' . $language);
        }
        $metaFields = $app->getUserMetaDataFields(true);
        $appSettings = app(AppSettings::class);
        $appSettingValues = [];
        foreach($appSettings->readonlySettings() as $settingKey) {
            $appSettingValues[$settingKey] = $appSettings->getValue($settingKey);
        }
        $defaultAppProfile = $app->getDefaultAppProfile();
        $appProfileSettingValues = $app->profiles->mapWithKeys(function ($appProfile) {
            return [$appProfile->id => collect(AppProfileSettings::$settings)->keys()->mapWithKeys(function ($setting) use ($appProfile) {
                return [$setting => $appProfile->getValue($setting, false, true)];
            })];
        });

        $user = Auth::user();
        return Response::json([
            'languages' => $languages,
            'metaFields' => $metaFields,
            'appSettings' => $appSettingValues,
            'defaultAppProfileId' => $defaultAppProfile->id,
            'appProfileSettings' => $appProfileSettingValues,
            'myRights' => $user->getAllRights(),
            'isFullAdmin' => $user->isFullAdmin(),
            'isMainAdmin' => $user->isMainAdmin(),
            'isSuperAdmin' => $user->isSuperAdmin(),
            'tagRights' => $user->tagRightsRelation->pluck('id'),
            'profiles' => $app->profiles,
            'allowMaillessSignup' => $app->allowMaillessSignup(),
            'userId' => $user->id,
            'hasNewQuestionPreview' => $app->hasNewQuestionPreview(),
            'app_hosted_at' => $app->app_hosted_at,
        ]);
    }

    /**
     * Returns details of all apps
     *
     * @return JsonResponse
     */
    public function getDetailsOfAllApps(): JsonResponse
    {
        $responseData = [];

        $apps = App::all();

        foreach ($apps as $app) {
            $statsEngine = app(StatsEngine::class, ['appId' => $app->id]);
            $responseData[$app->id] = [
                'active_users' => $statsEngine->getRecentlyActiveUserCount(),
                'running_games' => $statsEngine->runningGames(),
                'started_game_players' => $statsEngine->startedGamePlayers(),
            ];
        }

        return \Response::json([
            'details' => $responseData,
        ]);
    }
}
