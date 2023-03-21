<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseContentAttemptAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('course_content_attempt_attachments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('course_content_attempt_id')->index('attempt_id');
            $table->bigInteger('course_content_attachment_id')->index('attachment_id');
            $table->string('value');
            $table->boolean('passed')->nullable();
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
        Schema::dropIfExists('course_content_attempt_attachments');
    }
}
