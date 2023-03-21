<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQuestionIdFieldToTestSubmissionAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('test_submission_answers', function (Blueprint $table) {
            $table->integer('question_id')
                  ->references('id')
                  ->on('questions')
                  ->after('test_question_id');
            $table->integer('test_question_id')
                  ->nullable()
                  ->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('test_submission_answers', function (Blueprint $table) {
            $table->dropColumn('question_id');
            $table->integer('test_question_id')
                  ->nullable(false)
                  ->change();
        });
    }
}
