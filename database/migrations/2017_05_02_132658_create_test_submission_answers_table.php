<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestSubmissionAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('test_submission_answers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('test_question_id')
                  ->references('id')
                  ->on('test_questions');
            $table->integer('test_submission_id')
                  ->references('id')
                  ->on('test_submissions')
                  ->index('test_submission_id');
            $table->string('answer_ids')
                  ->nullable();
            $table->boolean('result')
                  ->nullable();
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
        Schema::dropIfExists('test_submission_answers');
    }
}
