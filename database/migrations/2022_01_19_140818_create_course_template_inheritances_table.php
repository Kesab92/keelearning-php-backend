<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseTemplateInheritancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('course_template_inheritances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id')->references('id')->on('courses')->index();
            $table->unsignedBigInteger('app_id')->references('id')->on('apps')->index();
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
        Schema::dropIfExists('course_template_inheritances');
    }
}
