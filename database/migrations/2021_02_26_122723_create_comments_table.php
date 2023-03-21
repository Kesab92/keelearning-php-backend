<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('app_id')->index();
            $table->bigInteger('author_id')->index();
            $table->text('body');
            $table->bigInteger('foreign_id')->index();
            $table->integer('foreign_type');
            $table->bigInteger('parent_id')->nullable();
            $table->bigInteger('deleted_by_id')->nullable();
            $table->dateTime('deleted_at')->index()->nullable();
            $table->timestamps();

            $table->index(['foreign_type', 'foreign_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comments');
    }
}
