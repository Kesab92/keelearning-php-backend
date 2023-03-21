<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameIsCanceledInAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->renameColumn('appointment_start_date', 'start_date');
            $table->renameColumn('appointment_end_date', 'end_date');
            $table->renameColumn('is_canceled', 'is_cancelled');

            $table->renameIndex('appointments_appointment_start_date_index', 'appointments_start_date_index');
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
            $table->renameColumn('start_date', 'appointment_start_date');
            $table->renameColumn('end_date', 'appointment_end_date');
            $table->renameColumn('is_cancelled', 'is_canceled');

            $table->renameIndex('appointments_start_date_index', 'appointments_appointment_start_date_index');
        });
    }
}
