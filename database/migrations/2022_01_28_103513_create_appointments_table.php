<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('app_id')->references('id')->on('apps')->index();
            $table->tinyInteger('type');
            $table->dateTime('appointment_start_date')->index();
            $table->dateTime('appointment_end_date');
            $table->dateTime('published_at')->nullable(true)->default(null);
            $table->boolean('is_active')->default(false);
            $table->boolean('is_canceled')->default(false);
            $table->boolean('has_reminder')->default(false)->index();
            $table->integer('reminder_time')->nullable(true);
            $table->integer('reminder_unit_type')->nullable(true);
            $table->string('location')->nullable(true);
            $table->unsignedBigInteger('created_by_id')->references('id')->on('users');
            $table->unsignedBigInteger('last_updated_by_id')->references('id')->on('users');
            $table->integer('invitation_mode')->nullable(true);
            $table->boolean('has_specific_user_invitations')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appointments');
    }
}
