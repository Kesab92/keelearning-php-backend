<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionsDifficultyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('question_difficulties', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('question_id')->references('id')->on('questions');
            $table->unsignedInteger('user_id')->nullable()->references('id')->on('users');
            $table->decimal('difficulty', 8, 7)->default(0);
            $table->unsignedBigInteger('sample_size')->default(0);
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
        Schema::dropIfExists('question_difficulties');
    }
}
