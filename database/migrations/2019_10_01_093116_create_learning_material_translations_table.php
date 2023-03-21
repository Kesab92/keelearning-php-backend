<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLearningMaterialTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('learning_material_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('learning_material_id')->references('id')->on('learning_materials');
            $table->string('language');
            $table->string('title');
            $table->text('description');
            $table->text('link');
            $table->string('file');
            $table->string('file_type');
            $table->timestamps();
        });
        Artisan::call('translations:migrate', [
            'model' => 'LearningMaterial',
        ]);
        Schema::table('learning_materials', function (Blueprint $table) {
            $table->dropColumn('title');
            $table->dropColumn('description');
            $table->dropColumn('link');
            $table->dropColumn('file');
            $table->dropColumn('file_type');
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
        Schema::drop('learning_material_translations');
        Schema::table('learning_materials', function (Blueprint $table) {
            $table->string('title');
            $table->text('description');
            $table->text('link');
            $table->string('file');
            $table->string('file_type');
        });
    }
}
