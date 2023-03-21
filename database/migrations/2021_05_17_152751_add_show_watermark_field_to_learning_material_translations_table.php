<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShowWatermarkFieldToLearningMaterialTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('learning_material_translations', function (Blueprint $table) {
            $table->boolean('show_watermark')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('learning_material_translations', function (Blueprint $table) {
            $table->dropColumn('show_watermark');
        });
    }
}
