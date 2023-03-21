<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFileSizeKbToLearningMaterialTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('learning_material_translations', function (Blueprint $table) {
            $table->unsignedInteger('file_size_kb')->nullable()->after('file_type');
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
            $table->dropColumn('file_size_kb');
        });
    }
}
