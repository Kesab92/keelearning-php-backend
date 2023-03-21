<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndizesToQuestionAnswerTranslations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('question_answer_translations', function (Blueprint $table) {
            $table->index('question_answer_id');
            $table->index('language');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('question_answer_translations', function (Blueprint $table) {
            $table->dropIndex('question_answer_translations_language_index');
            $table->dropIndex('question_answer_translations_question_answer_id_index');
        });
    }
}
