<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTestsTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('test_translations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('test_id')->references('id')->on('categories')->index();
            $table->string('language');
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });
        Artisan::call('translations:migrate', [
            'model' => 'Test',
        ]);
        Schema::table('tests', function (Blueprint $table) {
            $table->dropColumn('name');
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
        Schema::drop('test_translations');
        Schema::table('tests', function (Blueprint $table) {
            $table->string('name');
            $table->text('description')->nullable();
        });
    }
}
