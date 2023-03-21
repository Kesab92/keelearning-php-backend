<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppointmentTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appointment_translations', function (Blueprint $table) {
            $table->id();
            $table->string('language');
            $table->unsignedBigInteger('appointment_id')->references('id')->on('appointments')->index();
            $table->string('name');
            $table->text('description')->nullable(true);
            $table->text('cover_image_url')->nullable(true);
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
        Schema::dropIfExists('appointment_translations');
    }
}
