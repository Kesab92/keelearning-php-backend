<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseContentAttemptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('course_content_attempts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('course_content_id')->index();
            $table->bigInteger('course_participation_id')->index();
            $table->dateTime('finished_at')->nullable();
            $table->boolean('passed')->nullable()->index();
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
        Schema::dropIfExists('course_content_attempts');
    }
}
