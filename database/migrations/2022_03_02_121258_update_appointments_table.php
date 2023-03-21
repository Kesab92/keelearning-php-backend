<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('is_active');
            $table->dropColumn('invitation_mode');
            $table->dropColumn('has_specific_user_invitations');
            $table->boolean('is_draft')->default(true);
            $table->dateTime('send_reminder_at')->nullable(true)->default(null);
            $table->boolean('send_notification')->default(false);
            $table->dateTime('last_notification_sent_at')->nullable(true)->default(null);

            $table->index(['app_id', 'published_at']);
            $table->index(['app_id', 'is_draft', 'send_reminder_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->boolean('is_active')->default(false);
            $table->integer('invitation_mode')->nullable(true);
            $table->boolean('has_specific_user_invitations')->default(false);
            $table->dropColumn('is_draft');
            $table->dropColumn('send_reminder_at');
            $table->dropColumn('send_notification');
            $table->dropColumn('last_notification_sent_at');

            $table->dropIndex('appointments_app_id_published_at_index');
            $table->dropIndex('appointments_app_id_is_draft_send_reminder_at_index');
        });
    }
}
