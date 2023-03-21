<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseContentAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('course_content_attachments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('course_content_id')->index();
            $table->integer('position');
            $table->integer('type')->index();
            $table->bigInteger('foreign_id')->index();
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
        Schema::dropIfExists('course_content_attachments');
    }
}
