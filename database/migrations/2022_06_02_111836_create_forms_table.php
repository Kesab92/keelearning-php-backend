<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('app_id')->references('id')->on('apps')->index();
            $table->boolean('is_draft')->default(true);
            $table->boolean('is_archived')->default(false);
            $table->unsignedBigInteger('created_by_id')->references('id')->on('users');
            $table->unsignedBigInteger('last_updated_by_id')->references('id')->on('users');
            $table->timestamps();
        });

        Schema::create('form_translations', function (Blueprint $table) {
            $table->id();
            $table->string('language');
            $table->unsignedBigInteger('form_id')->references('id')->on('forms')->index();
            $table->string('title');
            $table->text('cover_image_url');
            $table->timestamps();
        });

        Schema::create('form_tags', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('form_id')->references('id')->on('forms')->index();
            $table->unsignedBigInteger('tag_id')->references('id')->on('tags')->index();
            $table->timestamps();
        });

        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('form_id')->references('id')->on('forms')->index();
            $table->boolean('is_required')->default(false);
            $table->tinyInteger('type');
            $table->tinyInteger('order');
            $table->timestamps();
        });

        Schema::create('form_field_translations', function (Blueprint $table) {
            $table->id();
            $table->string('language');
            $table->unsignedBigInteger('form_field_id')->references('id')->on('form_fields')->index();
            $table->string('title');
            $table->timestamps();
        });

        Schema::create('form_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('form_id')->references('id')->on('forms')->index();
            $table->unsignedBigInteger('user_id')->references('id')->on('users')->index();
            $table->timestamps();
        });

        Schema::create('form_answer_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('form_answer_id')->references('id')->on('form_answers')->index();
            $table->unsignedBigInteger('form_field_id')->references('id')->on('form_fields')->index();
            $table->text('answer');
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
        Schema::dropIfExists('form_answer_fields');
        Schema::dropIfExists('form_answers');
        Schema::dropIfExists('form_field_translations');
        Schema::dropIfExists('form_fields');
        Schema::dropIfExists('form_tags');
        Schema::dropIfExists('form_translations');
        Schema::dropIfExists('forms');
    }
}
