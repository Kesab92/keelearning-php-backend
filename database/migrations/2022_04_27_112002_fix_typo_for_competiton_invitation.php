<?php

use App\Models\MailTemplate;
use Illuminate\Database\Migrations\Migration;

class FixTypoForCompetitonInvitation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        MailTemplate
            ::where('type', 'CompetitonInvitation')
            ->update(['type' => 'CompetitionInvitation']);
        DB::table('mail_notification_user_settings')
            ->where('mail', 'CompetitonInvitation')
            ->update(['mail' => 'CompetitionInvitation']);
        DB::table('mail_notification_settings')
            ->where('mail', 'CompetitonInvitation')
            ->update(['mail' => 'CompetitionInvitation']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        MailTemplate
            ::where('type', 'CompetitionInvitation')
            ->update(['type' => 'CompetitonInvitation']);
        DB::table('mail_notification_user_settings')
            ->where('mail', 'CompetitionInvitation')
            ->update(['mail' => 'CompetitonInvitation']);
        DB::table('mail_notification_settings')
            ->where('mail', 'CompetitionInvitation')
            ->update(['mail' => 'CompetitonInvitation']);
    }
}
