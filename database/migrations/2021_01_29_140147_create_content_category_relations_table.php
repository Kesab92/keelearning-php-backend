<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentCategoryRelationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('content_category_relations', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('content_category_id')->index();
            $table->bigInteger('foreign_id');
            $table->string('type');
            $table->index(['content_category_id', 'foreign_id']);
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
        Schema::dropIfExists('content_category_relations');
    }
}
