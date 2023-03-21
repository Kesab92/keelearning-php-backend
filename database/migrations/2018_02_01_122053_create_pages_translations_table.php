<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagesTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('page_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('page_id')->references('id')->on('pages');
            $table->string('language');
            $table->string('title');
            $table->text('content');
            $table->timestamps();
        });
        Artisan::call('translations:migrate', [
            'model' => 'Page',
        ]);
        Schema::table('pages', function (Blueprint $table) {
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
        Schema::drop('page_translations');
        Schema::table('pages', function (Blueprint $table) {
            $table->string('title');
            $table->text('content');
        });
    }
}
