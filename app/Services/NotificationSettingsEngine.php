<?php

namespace App\Services;

use App\Models\AppProfile;
use App\Models\UserNotificationSetting;
use App\Models\User;
use Illuminate\Support\Collection;
use Str;

class NotificationSettingsEngine
{
    /**
     * Returns a collection with all available notification types
     *
     * @return Collection
     */
    public function getNotificationTypes(): Collection
    {
        return collect(AppProfileSettings::$settings)
            ->filter(function ($setting) {
                return $setting['type'] == 'notification';
            })
            ->keys()
            ->map(function ($setting) {
                return Str::remove(['notification_', '_enabled', '_user_manageable'], $setting);
            })
            ->unique()
            ->prepend('all')
            ->values();
    }

    /**
     *  Returns all mail notification settings for a user
     *
     * @param User $user
     * @return Collection
     */
    public function getUserNotificationSettings(User $user): Collection
    {
        $appProfile = $user->getAppProfile();

        $userNotificationSettings = UserNotificationSetting::where('user_id', $user->id)
            ->get()
            ->keyBy('notification');

        return $this->getNotificationTypes()
            ->map(function ($notification) use ($appProfile, $userNotificationSettings) {
                $userNotificationSetting = $userNotificationSettings->get($notification);

                // the deactivation option should be disabled if the necessary module is disabled for the profile or the app
                $allowedToDeactivate = $this->isNecessaryModuleEnabled($notification, $appProfile);

                if($allowedToDeactivate) {
                    $allowedToDeactivate = $appProfile->canNotificationBeDisabledByUser($notification);
                }

                return [
                    'enabled'             => $appProfile->getValue('notification_' . $notification . '_enabled', false, true),
                    'allowedToDeactivate' => $allowedToDeactivate,
                    'notification'        => $notification,
                    'mail_disabled'       => $userNotificationSetting ? $userNotificationSetting->mail_disabled : false,
                    'push_disabled'       => $userNotificationSetting ? $userNotificationSetting->push_disabled : false,
                ];
            });
    }

    /**
     * Checks if the necessary module of the notification is enabled
     * @param string $notification
     * @param AppProfile $appProfile
     * @return bool
     */
    private function isNecessaryModuleEnabled(string $notification, AppProfile $appProfile):bool {
        $necessaryModules = [
            'AppointmentReminder' => 'module_appointments',
            'AppointmentStartDateWasUpdated' => 'module_appointments',
            'AppQuestionSuggestion' => 'module_appointments',
            'CommentDeletedForAuthor' => 'module_comments',
            'CommentDeletedForReporter' => 'module_comments',
            'CommentNotDeleted' => 'module_comments',
            'CompetitionReminder' => 'module_competitions',
            'CompetitionResult' => 'module_competitions',
            'CompetitionInvitation' => 'module_competitions',
            'CourseAccessRequest' => 'module_courses',
            'CourseReminder' => 'module_courses',
            'GameAbort' => 'module_quiz',
            'GameDrawInfo' => 'module_quiz',
            'GameInvitation' => 'module_quiz',
            'GameLostInfo' => 'module_quiz',
            'GameReminder' => 'module_quiz',
            'GameWonInfo' => 'module_quiz',
            'LearningMaterialsPublished' => 'module_learningmaterials',
            'NewAppointment' => 'module_appointments',
            'NewCourseNotification' => 'module_courses',
            'NewsPublishedInfo' => 'module_suggested_questions',
            'PassedCourse' => 'module_courses',
            'RepetitionCourseReminder' => 'module_courses',
            'TestPassed' => 'module_tests',
            'TestReminder' => 'module_tests',
            'QuizTeamAdd' => 'module_quiz',
            'WebinarReminder' => 'module_webinars',
        ];

        if(!array_key_exists($notification, $necessaryModules)) {
            return true;
        }
        if(!$appProfile->getValue($necessaryModules[$notification], false, true)) {
            return false;
        }
        $appSetting = app(AppSettings::class);
        if(!$appSetting->getValue($necessaryModules[$notification])) {
            return false;
        }

        return true;
    }
}
