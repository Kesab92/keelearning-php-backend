<?php

use App\Models\UserNotificationSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ExtendMailNotificationUserSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('mail_notification_user_settings', 'user_notification_settings');
        Schema::table('user_notification_settings', function (Blueprint $table) {
            $table->boolean('mail_disabled')
                ->default(true)
                ->after('mail');
            $table->boolean('push_disabled')
                ->default(false)
                ->after('mail');
            $table->index('mail');
            $table->renameColumn('mail', 'notification');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // since we can only disable mails now
        UserNotificationSetting::where('mail_disabled', 0)->delete();

        Schema::table('user_notification_settings', function (Blueprint $table) {
            $table->dropIndex('user_notification_settings_mail_index');
            $table->renameColumn('notification', 'mail');
            $table->dropColumn('push_disabled');
            $table->dropColumn('mail_disabled');
        });
        Schema::rename('user_notification_settings', 'mail_notification_user_settings');
    }
}
