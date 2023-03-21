<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppointmentTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appointment_tags', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('appointment_id')->references('id')->on('appointments')->index();
            $table->unsignedBigInteger('tag_id')->references('id')->on('tags')->index();
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
        Schema::dropIfExists('appointment_tags');
    }
}
