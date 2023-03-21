<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnswerTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('question_answer_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('question_answer_id')->references('id')->on('question_answers');
            $table->string('language');
            $table->string('content');
            $table->string('feedback')->nullable();
            $table->timestamps();
        });
        Artisan::call('translations:migrate', [
            'model' => 'QuestionAnswer',
        ]);
        Schema::table('question_answers', function (Blueprint $table) {
            $table->dropColumn('content');
            $table->dropColumn('feedback');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (env('APP_ENV') !== 'testing') {
            die('Hold up, friend! You will need to manually roll back this migration or you lose a lot of data.');
        }
        Schema::drop('question_answer_translations');
        Schema::table('question_answers', function (Blueprint $table) {
            $table->string('content');
            $table->string('feedback')->nullable();
        });
    }
}
