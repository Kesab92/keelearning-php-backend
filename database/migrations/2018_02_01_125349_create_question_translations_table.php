<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('question_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('question_id')->references('id')->on('questions');
            $table->string('language');
            $table->string('title');
            $table->string('latex')->nullable();
            $table->timestamps();
        });
        Artisan::call('translations:migrate', [
            'model' => 'Question',
        ]);
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('title');
            $table->dropColumn('latex');
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
        Schema::drop('question_translations');
        Schema::table('questions', function (Blueprint $table) {
            $table->string('title');
            $table->string('latex')->nullable();
        });
    }
}
