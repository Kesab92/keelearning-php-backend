<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewsTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('news_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('news_id')->references('id')->on('news');
            $table->string('language');
            $table->string('title');
            $table->text('content');
            $table->timestamps();
        });
        Artisan::call('translations:migrate', [
            'model' => 'News',
        ]);
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn('title');
            $table->dropColumn('content');
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
        Schema::drop('news_translations');
        Schema::table('news', function (Blueprint $table) {
            $table->string('title');
            $table->text('content');
        });
    }
}
