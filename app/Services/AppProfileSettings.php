<?php

namespace App\Services;

use App\Exceptions\Settings\DuplicationException;
use App\Models\App;
use App\Models\AppProfileSetting;

/**
 * Class AppSettings
 * Functionality to control app profile specific settings.
 */
class AppProfileSettings
{
    private int $profileId;
    private static array $_cache = [];

    public function __construct(int $profileId)
    {
        $this->profileId = $profileId;
    }

    public static $settings = [
        'color_primary' => [
            'access' => 'admin',
            'default' => '#19bfd4',
            'type' => 'color',
        ],
        'color_secondary' => [
            'access' => 'admin',
            'default' => '#ff4c81',
            'type' => 'color',
        ],
        'color_success' => [
            'access' => 'superadmin',
            'default' => '#00ca91',
            'type' => 'color',
        ],
        'color_medium_success' => [
            'access' => 'superadmin',
            'default' => '#94ca00',
            'type' => 'color',
        ],
        'color_error' => [
            'access' => 'superadmin',
            'default' => '#f74f4f',
            'type' => 'color',
        ],
        'color_gold' => [
            'access' => 'superadmin',
            'default' => '#fecc00',
            'type' => 'color',
        ],
        'color_silver' => [
            'access' => 'superadmin',
            'default' => '#afb9bf',
            'type' => 'color',
        ],
        'color_bronze' => [
            'access' => 'superadmin',
            'default' => '#de906f',
            'type' => 'color',
        ],
        'color_highlight' => [
            'access' => 'superadmin',
            'default' => '#fdedb7',
            'type' => 'color',
        ],
        'color_dark' => [
            'access' => 'superadmin',
            'default' => '#3d3d53',
            'type' => 'color',
        ],
        'color_dark_medium_emphasis' => [
            'access' => 'superadmin',
            'default' => '#797e8b',
            'type' => 'color',
        ],
        'color_dark_light_emphasis' => [
            'access' => 'superadmin',
            'default' => '#9fa9b1',
            'type' => 'color',
        ],
        'color_white' => [
            'access' => 'superadmin',
            'default' => '#ffffff',
            'type' => 'color',
        ],
        'color_soft_highlight' => [
            'access' => 'superadmin',
            'default' => '#f4f6fa',
            'type' => 'color',
        ],
        'color_divider' => [
            'access' => 'superadmin',
            'default' => '#e4e8eb',
            'type' => 'color',
        ],
        'color_text_highlight' => [
            'access' => 'superadmin',
            'default' => '#fdedb7',
            'type' => 'color',
        ],
        'tablet_light_background' => [
            'access' => 'admin',
            'default' => false,
            'type' => 'setting',
        ],
        'app_name' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
        ],
        'app_name_short' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
        ],
        'subdomain' => [
            'access' => 'superadmin',
            'default' => '',
            'type' => 'setting',
        ],
        'external_domain' => [
            'access' => 'superadmin',
            'default' => '',
            'type' => 'setting',
        ],
        'allow_custom_avatars' => [
            'access' => 'admin',
            'default' => '1',
            'type' => 'setting',
        ],
        'module_news' => [
            'access' => 'admin',
            'default' => false,
            'type' => 'module',
        ],
        'module_learningmaterials' => [
            'access' => 'admin',
            'default' => false,
            'type' => 'module',
        ],
        'module_powerlearning' => [
            'access' => 'admin',
            'default' => false,
            'type' => 'module',
        ],
        'module_indexcards' => [
            'access' => 'admin',
            'default' => false,
            'type' => 'module',
        ],
        'module_quiz' => [
            'access' => 'admin',
            'default' => false,
            'type' => 'module',
        ],
        'module_bots' => [
            'access' => 'admin',
            'default' => false,
            'type' => 'module',
        ],
        'module_quiz_teams' => [
            'access' => 'admin',
            'default' => false,
            'type' => 'module',
        ],
        'module_suggested_questions' => [
            'access' => 'admin',
            'default' => false,
            'type' => 'module',
        ],
        'module_competitions' => [
            'access' => 'admin',
            'default' => false,
            'type' => 'module',
        ],
        'module_tests' => [
            'access' => 'admin',
            'default' => false,
            'type' => 'module',
        ],
        'module_webinars' => [
            'access' => 'admin',
            'default' => false,
            'type' => 'module',
        ],
        'module_courses' => [
            'access' => 'admin',
            'default' => false,
            'type' => 'module',
        ],
        'module_vouchers' => [
            'access' => 'admin',
            'default' => false,
            'type' => 'module',
        ],
        'module_advertisements' => [
            'access' => 'admin',
            'default' => false,
            'type' => 'module',
        ],
        'module_keywords' => [
            'access' => 'admin',
            'default' => false,
            'type' => 'module',
        ],
        'module_comments' => [
            'access' => 'admin',
            'default' => false,
            'type' => 'module',
        ],
        'module_appointments' => [
            'access' => 'admin',
            'default' => false,
            'type' => 'module',
        ],
        'module_todolists' => [
            'access' => 'admin',
            'default' => false,
            'type' => 'module',
        ],
        'notification_AppointmentReminder_enabled' => [
            'access' => 'admin',
            'default' => true,
            'type' => 'notification',
        ],
        'notification_AppointmentReminder_user_manageable' => [
            'access' => 'admin',
            'default' => false,
            'type' => 'notification',
        ],
        'notification_AppointmentStartDateWasUpdated_enabled' => [
            'access' => 'admin',
            'default' => true,
            'type' => 'notification',
        ],
        'notification_AppointmentStartDateWasUpdated_user_manageable' => [
            'access' => 'admin',
            'default' => false,
            'type' => 'notification',
        ],
        'notification_AppReminder_enabled' => [
            'access' => 'admin',
            'default' => true,
            'type' => 'notification',
        ],
        'notification_AppReminder_user_manageable' => [
            'access' => 'admin',
            'default' => true,
            'type' => 'notification',
        ],
        'notification_AppQuestionSuggestion_enabled' => [
            'access' => 'admin',
            'default' => true,
            'type' => 'notification',
        ],
        'notification_AppQuestionSuggestion_user_manageable' => [
            'access' => 'admin',
            'default' => false,
            'type' => 'notification',
        ],
        'notification_CommentDeletedForAuthor_enabled' => [
            'access' => 'admin',
            'default' => true,
            'type' => 'notification',
        ],
        'notification_CommentDeletedForAuthor_user_manageable' => [
            'access' => 'admin',
            'default' => false,
            'type' => 'notification',
        ],
        'notification_CommentDeletedForReporter_enabled' => [
            'access' => 'admin',
            'default' => true,
            'type' => 'notification',
        ],
        'notification_CommentDeletedForReporter_user_manageable' => [
            'access' => 'admin',
            'default' => false,
            'type' => 'notification',
        ],
        'notification_CommentNotDeleted_enabled' => [
            'access' => 'admin',
            'default' => true,
            'type' => 'notification',
        ],
        'notification_CommentNotDeleted_user_manageable' => [
            'access' => 'admin',
            'default' => true,
            'type' => 'notification',
        ],
        'notification_CompetitionReminder_enabled' => [
            'access' => 'admin',
            'default' => true,
            'type' => 'notification',
        ],
        'notification_CompetitionReminder_user_manageable' => [
            'access' => 'admin',
            'default' => true,
            'type' => 'notification',
        ],
        'notification_CompetitionResult_enabled' => [
            'access' => 'admin',
            'default' => true,
            'type' => 'notification',
        ],
        'notification_CompetitionResult_user_manageable' => [
            'access' => 'admin',
            'default' => false,
            'type' => 'notification',
        ],
        'notification_CompetitionInvitation_enabled' => [
            'access' => 'admin',
            'default' => true,
            'type' => 'notification',
        ],
        'notification_CompetitionInvitation_user_manageable' => [
            'access' => 'admin',
            'default' => false,
            'type' => 'notification',
        ],
        'notification_CourseAccessRequest_enabled' => [
            'access' => 'admin',
            'default' => true,
            'type' => 'notification',
        ],
        'notification_CourseAccessRequest_user_manageable' => [
            'access' => 'admin',
            'default' => false,
            'type' => 'notification',
        ],
        'notification_CourseReminder_enabled' => [
            'access' => 'admin',
            'default' => true,
            'type' => 'notification',
        ],
        'notification_CourseReminder_user_manageable' => [
            'access' => 'admin',
            'default' => false,
            'type' => 'notification',
        ],
        'notification_ExpirationReminder_enabled' => [
            'access' => 'admin',
            'default' => true,
            'type' => 'notification',
        ],
        'notification_ExpirationReminder_user_manageable' => [
            'access' => 'admin',
            'default' => false,
            'type' => 'notification',
        ],
        'notification_GameAbort_enabled' => [
            'access' => 'admin',
            'default' => true,
            'type' => 'notification',
        ],
        'notification_GameAbort_user_manageable' => [
            'access' => 'admin',
            'default' => true,
            'type' => 'notification',
        ],
        'notification_GameDrawInfo_enabled' => [
            'access' => 'admin',
            'default' => true,
            'type' => 'notification',
        ],
        'notification_GameDrawInfo_user_manageable' => [
            'access' => 'admin',
            'default' => true,
            'type' => 'notification',
        ],
        'notification_GameInvitation_enabled' => [
            'access' => 'admin',
            'default' => true,
            'type' => 'notification',
        ],
        'notification_GameInvitation_user_manageable' => [
            'access' => 'admin',
            'default' => false,
            'type' => 'notification',
        ],
        'notification_GameLostInfo_enabled' => [
            'access' => 'admin',
            'default' => true,
            'type' => 'notification',
        ],
        'notification_GameLostInfo_user_manageable' => [
            'access' => 'admin',
            'default' => true,
            'type' => 'notification',
        ],
        'notification_GameReminder_enabled' => [
            'access' => 'admin',
            'default' => true,
            'type' => 'notification',
        ],
        'notification_GameReminder_user_manageable' => [
            'access' => 'admin',
            'default' => false,
            'type' => 'notification',
        ],
        'notification_GameWonInfo_enabled' => [
            'access' => 'admin',
            'default' => true,
            'type' => 'notification',
        ],
        'notification_GameWonInfo_user_manageable' => [
            'access' => 'admin',
            'default' => true,
            'type' => 'notification',
        ],
        'notification_LearningMaterialsPublished_enabled' => [
            'access' => 'admin',
            'default' => true,
            'type' => 'notification',
        ],
        'notification_LearningMaterialsPublished_user_manageable' => [
            'access' => 'admin',
            'default' => false,
            'type' => 'notification',
        ],
        'notification_NewAppointment_enabled' => [
            'access' => 'admin',
            'default' => true,
            'type' => 'notification',
        ],
        'notification_NewAppointment_user_manageable' => [
            'access' => 'admin',
            'default' => false,
            'type' => 'notification',
        ],
        'notification_NewCourseNotification_enabled' => [
            'access' => 'admin',
            'default' => true,
            'type' => 'notification',
        ],
        'notification_NewCourseNotification_user_manageable' => [
            'access' => 'admin',
            'default' => false,
            'type' => 'notification',
        ],
        'notification_NewsPublishedInfo_enabled' => [
            'access' => 'admin',
            'default' => true,
            'type' => 'notification',
        ],
        'notification_NewsPublishedInfo_user_manageable' => [
            'access' => 'admin',
            'default' => false,
            'type' => 'notification',
        ],
        'notification_PassedCourse_enabled' => [
            'access' => 'admin',
            'default' => true,
            'type' => 'notification',
        ],
        'notification_PassedCourse_user_manageable' => [
            'access' => 'admin',
            'default' => true,
            'type' => 'notification',
        ],
        'notification_RepetitionCourseReminder_enabled' => [
            'access' => 'admin',
            'default' => true,
            'type' => 'notification',
        ],
        'notification_RepetitionCourseReminder_user_manageable' => [
            'access' => 'admin',
            'default' => false,
            'type' => 'notification',
        ],
        'notification_SubscriptionComment_enabled' => [
            'access' => 'admin',
            'default' => true,
            'type' => 'notification',
        ],
        'notification_SubscriptionComment_user_manageable' => [
            'access' => 'admin',
            'default' => true,
            'type' => 'notification',
        ],
        'notification_TestPassed_enabled' => [
            'access' => 'admin',
            'default' => true,
            'type' => 'notification',
        ],
        'notification_TestPassed_user_manageable' => [
            'access' => 'admin',
            'default' => true,
            'type' => 'notification',
        ],
        'notification_TestReminder_enabled' => [
            'access' => 'admin',
            'default' => true,
            'type' => 'notification',
        ],
        'notification_TestReminder_user_manageable' => [
            'access' => 'admin',
            'default' => false,
            'type' => 'notification',
        ],
        'notification_QuizTeamAdd_enabled' => [
            'access' => 'admin',
            'default' => true,
            'type' => 'notification',
        ],
        'notification_QuizTeamAdd_user_manageable' => [
            'access' => 'admin',
            'default' => true,
            'type' => 'notification',
        ],
        'notification_WebinarReminder_enabled' => [
            'access' => 'admin',
            'default' => true,
            'type' => 'notification',
        ],
        'notification_WebinarReminder_user_manageable' => [
            'access' => 'admin',
            'default' => false,
            'type' => 'notification',
        ],
        'quiz_users_choose_categories' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
        ],
        'quiz_enable_bots' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
        ],
        'bot_game_mails' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
        ],
        'hide_emails_frontend' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
        ],
        'quiz_hide_player_statistics' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
        ],
        'quiz_no_weekend_grace_period' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
        ],
        'quiz_round_answer_time' => [
            'access' => 'admin',
            'default' => 24,
            'type' => 'number',
        ],
        'quiz_round_initial_answer_time' => [
            'access' => 'admin',
            'default' => 72,
            'type' => 'number',
        ],
        'quiz_default_answer_time' => [
            'access' => 'admin',
            'default' => 15,
            'type' => 'number',
        ],
        'competitions_need_realname' => [
            'access' => 'superadmin',
            'default' => '',
            'type' => 'setting',
        ],
        'competitions_need_email' => [
            'access' => 'superadmin',
            'default' => '',
            'type' => 'setting',
        ],
        'hide_given_test_answers' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
        ],
        'signup_default_language' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
        ],
        'signup_enabled' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
        ],
        'signup_show_firstname' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
        ],
        'signup_show_firstname_mandatory' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
        ],
        'signup_show_lastname' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
        ],
        'signup_show_lastname_mandatory' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
        ],
        'signup_show_email' => [
            'access' => 'superadmin',
            'default' => '',
            'type' => 'setting',
        ],
        'signup_show_email_mandatory' => [
            'access' => 'superadmin',
            'default' => '',
            'type' => 'setting',
        ],
        'signup_show_voucher' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
        ],
        'signup_show_voucher_mandatory' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
        ],
        'signup_show_meta' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
        ],
        'signup_show_meta_mandatory' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
        ],
        'signup_has_temporary_accounts' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
        ],
        'signup_force_password_reset' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
        ],
        'allow_username_change' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
        ],
        'allow_email_change' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
        ],
        'app_icon' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
        ],
        'app_icon_no_transparency' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
        ],
        'app_logo' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
        ],
        'app_logo_inverse' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
        ],
        'app_logo_auth' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
        ],
        'auth_background_image' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
        ],
        'facebook_url' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
        ],
        'instagram_url' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
        ],
        'twitter_url' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
        ],
        'youtube_url' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
        ],
        'slug' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
        ],
        'enable_social_features' => [
            'access' => 'admin',
            'default' => '1',
            'type' => 'setting',
        ],
        'smtp_host' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
            'private' => true,
        ],
        'smtp_port' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'number',
            'private' => true,
        ],
        'smtp_username' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
            'private' => true,
        ],
        'smtp_password' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
            'private' => true,
        ],
        'smtp_email' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
            'private' => true,
        ],
        'smtp_encryption' => [
            'access' => 'admin',
            'default' => 'tls',
            'type' => 'setting',
            'private' => true,
        ],
        'contact_phone' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
        ],
        'contact_email' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
        ],
        'notification_mails' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
            'private' => true,
        ],
        'tos_id' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'number',
        ],
        'email_terms' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
            'private' => true,
        ],
        'ios_app_id' => [
            'access' => 'superadmin',
            'default' => '',
            'type' => 'setting',
        ],
        'android_app_id' => [
            'access' => 'superadmin',
            'default' => '',
            'type' => 'setting',
        ],
        'native_app_schema' => [
            'access' => 'superadmin',
            'default' => '',
            'type' => 'setting',
        ],
        'openid_enabled' => [
            'access' => 'admin',
            'default' => false,
            'type' => 'setting',
        ],
        'openid_title' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
        ],
        'openid_authority_url' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
            'private' => true,
        ],
        'openid_client_id' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
            'private' => true,
        ],
        'openid_claims' => [
            'access' => 'admin',
            'default' => '',
            'type' => 'setting',
            'private' => true,
        ],
        'enable_sso_registration' => [
            'access' => 'admin',
            'default' => true,
            'type' => 'setting',
        ],
        'sso_is_default_login' => [
            'access' => 'admin',
            'default' => false,
            'type' => 'setting',
        ],
        'max_concurrent_logins' => [
            'access' => 'admin',
            'default' => null,
            'type' => 'number',
        ],
        'use_real_name_as_displayname_frontend' => [
            'access' => 'admin',
            'default' => false,
            'type' => 'setting',
        ],
        'days_before_user_deletion' => [
            'access' => 'admin',
            'default' => null,
            'type' => 'setting',
        ]
    ];

    public function allowedSettings()
    {
        if (isSuperAdmin()) {
            return self::$settings;
        } else {
            return array_filter(self::$settings, function ($setting) {
                return $setting['access'] !== 'superadmin';
            });
        }
    }

    /**
     * Returns a specific setting for this app profile.
     *
     * @param $key
     *
     * @param bool $noDefault
     * @return null
     */
    public function getValue($key, $noDefault = false)
    {
        $values = $this->getCachedValues();
        if (isset($values[$key]) && $values[$key] !== '') {
            return $values[$key];
        }
        if (! $noDefault) {
            return self::$settings[$key]['default'];
        }

        return null;
    }

    /**
     * Sets a setting for this app profile.
     *
     * @param $key
     * @param $value
     *
     * @throws \Exception
     */
    public function setValue($key, $value)
    {
        $appProfileSetting = AppProfileSetting::firstOrNew(['app_profile_id' => $this->profileId, 'key' => $key]);
        if($value === null) {
            $value = '';
        }
        if($value && $this->mustBeUnique($key) && $this->valueExistsAlready($key, $value)) {
            throw new DuplicationException('The value "' . $value . '" for key "' . $key . '" already exists in a different app profile');
        }
        $appProfileSetting->value = $value;
        $appProfileSetting->save();

        if (isset(self::$_cache[$this->profileId])) {
            self::$_cache[$this->profileId][$key] = $value;
        }
    }

    private function valueExistsAlready($key, $value)
    {
        return AppProfileSetting
            ::where('key', $key)
            ->where('value', $value)
            ->where('app_profile_id', '!=', $this->profileId)
            ->count() > 0;
    }

    private function mustBeUnique($key)
    {
        return in_array($key, [
            'slug',
            'subdomain',
            'external_domain',
        ]);
    }

    /**
     * Fetches and caches (for this request) all app profile settings.
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    private function getCachedValues()
    {
        if (! isset(self::$_cache[$this->profileId])) {
            self::$_cache[$this->profileId] = AppProfileSetting::where('app_profile_id', $this->profileId)
                                      ->pluck('value', 'key');
        }

        return self::$_cache[$this->profileId];
    }

    /**
     * Clears the cache for this profile.
     */
    public function clearCache()
    {
        if (isset(self::$_cache[$this->profileId])) {
            unset(self::$_cache[$this->profileId]);
        }
    }
}
