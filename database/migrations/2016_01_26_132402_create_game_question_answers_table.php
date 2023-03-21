<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateGameQuestionAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game_question_answers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('game_question_id')->references('id')->on('game_questions');
            $table->integer('user_id')->references('id')->on('users');
            $table->integer('question_answer_id')->nullable()->references('id')->on('question_answers');

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
        Schema::drop('game_question_answers');
    }
}
