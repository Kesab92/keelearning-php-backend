<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCoverImageToLearningMaterialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('learning_materials', function (Blueprint $table) {
            $table->string('cover_image')->nullable();
            $table->dropColumn('file_is_cover');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('learning_materials', function (Blueprint $table) {
            $table->dropColumn('cover_image');
            $table->boolean('file_is_cover')->default(false);
        });
    }
}
