<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTrainingAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('training_answers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')
                  ->references('id')
                  ->on('users');
            $table->integer('question_id')
                  ->references('id')
                  ->on('questions');
            $table->string('answer_ids');
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
        Schema::drop('training_answers');
    }
}
