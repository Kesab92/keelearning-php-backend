<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCompetitionTagTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('competition_tag', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('competition_id');
            $table->integer('tag_id');
            $table->index('tag_id');
            $table->index('competition_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('competition_tag');
    }
}
