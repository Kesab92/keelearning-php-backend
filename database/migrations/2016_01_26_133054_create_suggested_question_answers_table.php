<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSuggestedQuestionAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('suggested_question_answers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('suggested_question_id')->references('id')->on('suggested_questions');
            $table->string('content');
            $table->boolean('correct');

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
        Schema::drop('suggested_question_answers');
    }
}
