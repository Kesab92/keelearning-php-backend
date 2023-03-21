<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class NewFilesystemColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->renameColumn('image_url', 'cover_image');
            $table->renameColumn('icon_url', 'category_icon');
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->text('cover_image_url')->nullable()->after('cover_image');
            $table->text('category_icon_url')->nullable()->after('category_icon');
        });
        Schema::table('certificate_templates', function (Blueprint $table) {
            $table->renameColumn('backgroundImage', 'background_image');
            $table->renameColumn('backgroundImageSize', 'background_image_size');
        });
        Schema::table('competitions', function (Blueprint $table) {
            $table->text('cover_image_url')->nullable()->after('cover_image');
        });
        Schema::table('index_cards', function (Blueprint $table) {
            $table->renameColumn('image_url', 'cover_image');
        });
        Schema::table('index_cards', function (Blueprint $table) {
            $table->text('cover_image_url')->nullable()->after('cover_image');
        });
        Schema::table('learning_material_folders', function (Blueprint $table) {
            $table->renameColumn('icon_url', 'folder_icon');
        });
        Schema::table('learning_material_folders', function (Blueprint $table) {
            $table->text('folder_icon_url')->nullable()->after('folder_icon');
        });
        Schema::table('learning_materials', function (Blueprint $table) {
            $table->text('cover_image_url')->nullable()->after('cover_image');
        });
        Schema::table('learning_material_translations', function (Blueprint $table) {
            $table->text('file_url')->nullable()->after('file');
        });
        Schema::table('news', function (Blueprint $table) {
            $table->text('cover_image_url')->nullable()->after('cover_image');
        });
        Schema::table('question_attachments', function (Blueprint $table) {
            $table->renameColumn('url', 'attachment');
        });
        Schema::table('question_attachments', function (Blueprint $table) {
            $table->text('attachment_url')->nullable()->after('attachment');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->text('avatar')->nullable();
            $table->text('avatar_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('cover_image_url');
            $table->dropColumn('category_icon_url');
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->renameColumn('cover_image', 'image_url');
            $table->renameColumn('category_icon', 'icon_url');
        });
        Schema::table('certificate_templates', function (Blueprint $table) {
            $table->renameColumn('background_image', 'backgroundImage');
            $table->renameColumn('background_image_size', 'backgroundImageSize');
        });
        Schema::table('competitions', function (Blueprint $table) {
            $table->dropColumn('cover_image_url');
        });
        Schema::table('index_cards', function (Blueprint $table) {
            $table->dropColumn('cover_image_url');
        });
        Schema::table('index_cards', function (Blueprint $table) {
            $table->renameColumn('cover_image', 'image_url');
        });
        Schema::table('learning_material_folders', function (Blueprint $table) {
            $table->dropColumn('folder_icon_url');
        });
        Schema::table('learning_material_folders', function (Blueprint $table) {
            $table->renameColumn('folder_icon', 'icon_url');
        });
        Schema::table('learning_materials', function (Blueprint $table) {
            $table->dropColumn('cover_image_url');
        });
        Schema::table('learning_material_translations', function (Blueprint $table) {
            $table->dropColumn('file_url');
        });
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn('cover_image_url');
        });
        Schema::table('question_attachments', function (Blueprint $table) {
            $table->dropColumn('attachment_url');
        });
        Schema::table('question_attachments', function (Blueprint $table) {
            $table->renameColumn('attachment', 'url');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('avatar');
            $table->dropColumn('avatar_url');
        });
    }
}
