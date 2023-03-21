<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQuestionTranslationIndizes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('question_translations', function (Blueprint $table) {
            $table->index('question_id');
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
        Schema::table('question_translations', function (Blueprint $table) {
            $table->dropIndex('question_id');
            $table->dropIndex('language');
        });
    }
}
