<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class IncreaseLengthOfQuestionTitleAndAnswer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('question_answer_translations', function ($table) {
            $table->string('content', 511)
                  ->change();
            $table->string('feedback', 1023)
                  ->change();
        });
        Schema::table('question_translations', function ($table) {
            $table->string('title', 511)
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
        Schema::table('question_answer_translations', function ($table) {
            $table->string('content', 255)
                  ->change();
            $table->string('feedback', 255)
                  ->change();
        });
        Schema::table('question_translations', function ($table) {
            $table->string('title', 255)
                  ->change();
        });
    }
}
