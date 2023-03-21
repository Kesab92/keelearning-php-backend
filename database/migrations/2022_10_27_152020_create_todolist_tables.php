<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTodolistTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('todolists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('app_id')->references('id')->on('apps')->index();
            $table->unsignedBigInteger('foreign_id')->references('id')->on('apps');
            $table->unsignedInteger('foreign_type');
            $table->timestamps();
            $table->index(['foreign_type', 'foreign_id']);
        });
        Schema::create('todolist_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('todolist_id')->references('id')->on('todolists')->index();
            $table->unsignedInteger('position');
            $table->timestamps();
        });
        Schema::create('todolist_item_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('todolist_item_id')->references('id')->on('todolist_items')->index();
            $table->string('language');
            $table->text('title');
            $table->text('description');
            $table->timestamps();
        });
        Schema::create('todolist_item_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('todolist_item_id')->references('id')->on('todolist_items');
            $table->unsignedBigInteger('user_id')->references('id')->on('users')->index();
            $table->index(['todolist_item_id', 'user_id']);
            $table->boolean('is_checked')->default(false);
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
        Schema::dropIfExists('todolists');
        Schema::dropIfExists('todolist_items');
        Schema::dropIfExists('todolist_item_translations');
        Schema::dropIfExists('todolist_item_answers');
    }
}
