<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebinarTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('webinar_tags', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('webinar_id')->references('id')->on('webinars');
            $table->unsignedBigInteger('tag_id')->references('id')->on('tags');
            $table->index('webinar_id');
            $table->index('tag_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('webinar_tags');
    }
}
