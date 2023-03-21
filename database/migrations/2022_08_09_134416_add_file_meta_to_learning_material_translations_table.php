<?php

use App\Models\LearningMaterialTranslation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFileMetaToLearningMaterialTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('learning_material_translations', function (Blueprint $table) {
            $table->tinyInteger('wbt_subtype')->nullable();
            $table->string('wbt_custom_entrypoint')->nullable();
        });
        foreach(LearningMaterialTranslation::where('file_type', 'wbt')->get() as $learningMaterialTranslation) {
            $learningMaterialTranslation->wbt_subtype = 'xapi';
            $learningMaterialTranslation->saveQuietly();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('learning_material_translations', function (Blueprint $table) {
            $table->dropColumn('wbt_subtype');
            $table->dropColumn('wbt_custom_entrypoint');
        });
    }
}
