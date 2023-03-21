<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoryGroupTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categorygroup_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('categorygroup_id')->references('id')->on('categorygroups');
            $table->string('language');
            $table->string('name');
            $table->timestamps();
        });
        Artisan::call('translations:migrate', [
            'model' => 'Categorygroup',
        ]);
        Schema::table('categorygroups', function (Blueprint $table) {
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
        Schema::drop('categorygroup_translations');
        Schema::table('categorygroups', function (Blueprint $table) {
            $table->string('name');
        });
    }
}
