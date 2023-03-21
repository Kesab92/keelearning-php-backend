<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLearningMaterialFolderTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('learning_material_folder_tags', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('learning_material_folder_id')
                ->references('id')
                ->on('learning_material_folders')
                ->index('learning_material_folder_id');
            $table->integer('tag_id')
                ->references('id')
                ->on('tags')
                ->index('tag_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('learning_material_folder_tags');
    }
}
