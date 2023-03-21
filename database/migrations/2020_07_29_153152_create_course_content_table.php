<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseContentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('course_contents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('course_chapter_id')->index();
            $table->integer('type')->index();
            $table->bigInteger('foreign_id')->nullable()->index();
            $table->integer('position');
            $table->boolean('visible');
            $table->integer('duration');
            $table->integer('pass_percentage')->nullable();
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
        Schema::dropIfExists('course_content');
    }
}
