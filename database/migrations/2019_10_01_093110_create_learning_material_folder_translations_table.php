<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLearningMaterialFolderTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('learning_material_folder_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('learning_material_folder_id')->references('id')->on('learning_material_folders');
            $table->string('language');
            $table->string('name');
            $table->timestamps();
        });
        Artisan::call('translations:migrate', [
            'model' => 'LearningMaterialFolder',
        ]);
        Schema::table('learning_material_folders', function (Blueprint $table) {
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
        Schema::drop('learning_material_folder_translations');
        Schema::table('learning_material_folders', function (Blueprint $table) {
            $table->string('name');
        });
    }
}
