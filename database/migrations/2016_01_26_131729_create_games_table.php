<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateGamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('games', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('app_id')->references('id')->on('apps');
            $table->integer('player1_id')->references('id')->on('users');
            $table->integer('player2_id')->references('id')->on('users');
            $table->boolean('player1_joker_available');
            $table->boolean('player2_joker_available');
            $table->integer('status');

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
        Schema::drop('games');
    }
}
