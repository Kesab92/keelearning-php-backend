<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateReminderInddicesInAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->index(['is_draft', 'is_cancelled', 'send_reminder_at']);
            $table->dropIndex('appointments_has_reminder_index');
            $table->dropIndex('appointments_app_id_is_draft_send_reminder_at_index');
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

            $table->index('has_reminder');
            $table->index(['app_id', 'is_draft', 'send_reminder_at']);
            $table->dropIndex('appointments_is_draft_is_cancelled_send_reminder_at_index');
        });
    }
}
