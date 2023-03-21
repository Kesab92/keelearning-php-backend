<?php

use App\Models\AppProfile;
use App\Models\AppProfileSetting;
use App\Models\AppSetting;
use App\Services\AppSettings;
use App\Services\AppProfileSettings;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MigrateNotificationSettings extends Migration
{
    const oldAppSettingToNewAppProfileSettings = [
        'block_mail_abort'               => ['notification_GameAbort_enabled'],
        'block_mail_competition'         => ['notification_CompetitionResult_enabled'],
        'block_mail_competitionreminder' => ['notification_CompetitionReminder_enabled'],
        'block_mail_game_finalize'       => [
            'notification_GameDrawInfo_enabled',
            'notification_GameLostInfo_enabled',
            'notification_GameWonInfo_enabled',
        ],
        'block_mail_invitation'          => ['notification_GameInvitation_enabled'],
        'block_mail_learning_materials'  => ['notification_LearningMaterialsPublished_enabled'],
        'block_mail_news'                => ['notification_NewsPublishedInfo_enabled'],
        'block_mail_quiz_team'           => ['notification_QuizTeamAdd_enabled'],
        'block_mail_reminder'            => ['notification_GameReminder_enabled'],
        'block_mail_test_passed'         => ['notification_TestPassed_enabled'],
        'block_mail_usagereminder'       => ['notification_AppReminder_enabled'],
        'block_mail_welcome'             => [], // cannot be disabled anymore
    ];

    const oldMailNotificationSettingToNewAppProfileSettings = [
        'AppReminder'                => 'notification_AppReminder_user_manageable',
        'CompetitionResult'          => 'notification_CompetitionResult_user_manageable',
        'CompetitionInvitation'      => 'notification_CompetitionInvitation_user_manageable',
        'ExpirationReminder'         => 'notification_ExpirationReminder_user_manageable',
        'GameAbort'                  => 'notification_GameAbort_user_manageable',
        'GameDrawInfo'               => 'notification_GameDrawInfo_user_manageable',
        'GameInvitation'             => 'notification_GameInvitation_user_manageable',
        'GameLostInfo'               => 'notification_GameLostInfo_user_manageable',
        'GameReminder'               => 'notification_GameReminder_user_manageable',
        'GameWonInfo'                => 'notification_GameWonInfo_user_manageable',
        'LearningMaterialsPublished' => 'notification_LearningMaterialsPublished_user_manageable',
        'NewsPublishedInfo'          => 'notification_NewsPublishedInfo_user_manageable',
        'QuizTeamAdd'                => 'notification_QuizTeamAdd_user_manageable',
        'TestPassed'                 => 'notification_TestPassed_user_manageable',
        'TestReminder'               => 'notification_TestReminder_user_manageable',
        'WebinarReminder'            => 'notification_WebinarReminder_user_manageable',
    ];
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (AppProfile::all() as $appProfile) {
            $appSettings = new AppSettings($appProfile->app_id);
            $appProfileSettings = new AppProfileSettings($appProfile->id);

            // migrate old app settings to new app profile settings
            foreach (self::oldAppSettingToNewAppProfileSettings as $oldAppSetting => $newAppProfileSettings) {
                $value = $appSettings->getValue($oldAppSetting);
                if ($value === '1') {
                    $value = true;
                }
                if ($value === '0') {
                    $value = false;
                }

                if ($value) {
                    foreach ($newAppProfileSettings as $newAppProfileSetting) {
                        $appProfileSettings->setValue($newAppProfileSetting, 0);
                    }
                }
            }

            // migrate mail notification settings to app profile settings
            $mailNotificationSettings = DB::table('mail_notification_settings')
                ->where('app_id', $appProfile->app_id)
                ->where('deactivatable', 1)
                ->pluck('mail');
            foreach($mailNotificationSettings as $mailNotificationSetting) {
                if (array_key_exists($mailNotificationSetting, self::oldMailNotificationSettingToNewAppProfileSettings)) {
                    $appProfileSettings->setValue(self::oldMailNotificationSettingToNewAppProfileSettings[$mailNotificationSetting], 1);
                }
            }
        }

        // delete old app settings
        AppSetting::whereIn('key', array_keys(self::oldAppSettingToNewAppProfileSettings))
            ->delete();
        // drop mail notification settings
        Schema::drop('mail_notification_settings');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // create mail notification settings table
        Schema::create('mail_notification_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('app_id')
                ->references('id')
                ->on('apps');
            $table->string('mail');
            $table->boolean('deactivatable');
            $table->timestamps();
        });

        // not worth the effort to implement rolling back notification & app settings
        // they'll just go back to their default values

        // delete app profile settings
        $settingKeys = collect(array_values(self::oldAppSettingToNewAppProfileSettings))
            ->flatten()
            ->merge(array_values(self::oldMailNotificationSettingToNewAppProfileSettings));
        AppProfileSetting::whereIn('key', $settingKeys)
            ->delete();
    }
}
