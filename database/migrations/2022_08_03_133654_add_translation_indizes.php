<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTranslationIndizes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('categorygroup_translations', function (Blueprint $table) {
            $table->index(['categorygroup_id', 'language']);
        });
        Schema::table('learning_material_folder_translations', function (Blueprint $table) {
            $table->index(['learning_material_folder_id', 'language'],'folder_id_language');
        });
        Schema::table('learning_material_translations', function (Blueprint $table) {
            $table->index(['learning_material_id', 'language'], 'material_id_language');
        });
        Schema::table('mail_template_translations', function (Blueprint $table) {
            $table->index(['mail_template_id', 'language']);
        });
        Schema::table('news_translations', function (Blueprint $table) {
            $table->index(['news_id', 'language']);
        });
        Schema::table('page_translations', function (Blueprint $table) {
            $table->index(['page_id', 'language']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('categorygroup_translations', function (Blueprint $table) {
            $table->dropIndex(['categorygroup_id', 'language']);
        });
        Schema::table('learning_material_folder_translations', function (Blueprint $table) {
            $table->dropIndex('folder_id_language');
        });
        Schema::table('learning_material_translations', function (Blueprint $table) {
            $table->dropIndex('material_id_language');
        });
        Schema::table('mail_template_translations', function (Blueprint $table) {
            $table->dropIndex(['mail_template_id', 'language']);
        });
        Schema::table('news_translations', function (Blueprint $table) {
            $table->dropIndex(['news_id', 'language']);
        });
        Schema::table('page_translations', function (Blueprint $table) {
            $table->dropIndex(['page_id', 'language']);
        });
    }
}
