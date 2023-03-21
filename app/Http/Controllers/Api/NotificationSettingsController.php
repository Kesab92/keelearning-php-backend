<?php

namespace App\Http\Controllers\Api;

use App\Http\APIError;
use App\Http\Controllers\Controller;
use App\Models\UserNotificationSetting;
use App\Services\NotificationSettingsEngine;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Response;

class NotificationSettingsController extends Controller
{
    /** @ deprecated */
    // this fixes a typo and also lists those mails
    // that used to exist before our refactoring
    const OLD_TO_NEW = [
        'AppReminder'                => 'AppReminder',
        'CompetitionReminder'        => 'CompetitionReminder',
        'CompetitionResult'          => 'CompetitionResult',
        'CompetitonInvitation'       => 'CompetitonInvitation', // FIXME: typo
        'ExpirationReminder'         => 'ExpirationReminder',
        'GameAbort'                  => 'GameAbort',
        'GameDrawInfo'               => 'GameDrawInfo',
        'GameInvitation'             => 'GameInvitation',
        'GameLostInfo'               => 'GameLostInfo',
        'GameReminder'               => 'GameReminder',
        'GameWonInfo'                => 'GameWonInfo',
        'LearningMaterialsPublished' => 'LearningMaterialsPublished',
        'NewsPublishedInfo'          => 'NewsPublishedInfo',
        'QuizTeamAdd'                => 'QuizTeamAdd',
        'TestPassed'                 => 'TestPassed',
        'TestReminder'               => 'TestReminder',
        'WebinarReminder'            => 'WebinarReminder',
    ];

    public function getSettings(NotificationSettingsEngine $notificationSettingsEngine): JsonResponse
    {
        return Response::json(['notifications' => $notificationSettingsEngine->getUserNotificationSettings(user())]);
    }

    public function updateSettings(NotificationSettingsEngine $notificationSettingsEngine, Request $request): JsonResponse
    {
        $this->validate($request, ['notifications' => 'required|array|min:1']);

        $validTypes = $notificationSettingsEngine
            ->getNotificationTypes();

        $notificationsInput = collect($request->input('notifications'))
            ->filter(function ($setting) use ($validTypes) {
                return (
                    isset($setting['notification'])
                    && $validTypes->contains($setting['notification'])
                    && (isset($setting['mail_disabled']) || isset($setting['push_disabled']))
                );
            });

        if ($notificationsInput->isEmpty()) {
            return new APIError(__('errors.data_invalid'), 400);
        }

        $userNotificationSettings = UserNotificationSetting::where('user_id', user()->id)
            ->whereIn('notification', $notificationsInput->pluck('notification'))
            ->get()
            ->keyBy('notification');

        foreach ($notificationsInput as $setting) {
            $userNotificationSetting = $userNotificationSettings->get($setting['notification']);

            if (!$userNotificationSetting) {
                $userNotificationSetting = new UserNotificationSetting();
                $userNotificationSetting->notification = $setting['notification'];
                $userNotificationSetting->user_id = user()->id;
            }
            if (isset($setting['mail_disabled'])) {
                $userNotificationSetting->mail_disabled = !!$setting['mail_disabled'];
            }
            if (isset($setting['push_disabled'])) {
                $userNotificationSetting->push_disabled = !!$setting['push_disabled'];
            }
            $userNotificationSetting->save();
        }

        return Response::json(['success' => true]);
    }


    /**
     * @deprecated
     * @param NotificationSettingsEngine $notificationSettingsEngine
     * @return JsonResponse
     * @throws Exception
     */
    public function getSettingsLegacy(NotificationSettingsEngine $notificationSettingsEngine): JsonResponse
    {
        $newToOld = array_flip(self::OLD_TO_NEW);
        $mailNotificationSettings = $notificationSettingsEngine->getUserNotificationSettings(user())
            ->filter(function($setting) use ($newToOld) {
                return array_key_exists($setting['notification'], $newToOld);
            })
            ->map(function($setting) use ($newToOld) {
                return [
                    'allowedToDeactivate' => $setting['allowedToDeactivate'],
                    'deactivated' => $setting['mail_disabled'],
                    'name' => $newToOld[$setting['notification']],
                ];
            })
            ->values();
        return Response::json($mailNotificationSettings);
    }

    /**
     * @deprecated
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function updateSettingsLegacy(Request $request): JsonResponse
    {
        $this->validate($request, ['settings' => 'required|array|min:1']);

        $settingsInput = collect($request->input('settings'))
            ->filter(function ($setting) {
                return $setting['name'] && array_key_exists($setting['name'], self::OLD_TO_NEW);
            })
            ->map(function ($setting) {
                return [
                    'name' => self::OLD_TO_NEW[$setting['name']],
                    'deactivated' => !!$setting['deactivated'],
                ];
            });

        $userNotificationSettings = UserNotificationSetting::where('user_id', user()->id)
            ->whereIn('notification', $settingsInput->pluck('name'))
            ->get();

        foreach ($settingsInput as $setting) {
            $userNotificationSetting = $userNotificationSettings
                ->where('notification', $setting['name'])
                ->first();
            if (!$userNotificationSetting) {
                $userNotificationSetting = new UserNotificationSetting();
                $userNotificationSetting->notification = $setting['name'];
                $userNotificationSetting->user_id = user()->id;
            }
            $userNotificationSetting->mail_disabled = $setting['deactivated'];
            $userNotificationSetting->save();
        }

        return Response::json(['success' => true]);
    }
}
