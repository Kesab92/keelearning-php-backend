<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGamePointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game_points', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')
                  ->references('id')
                  ->on('users')
                  ->index('user_id');
            $table->tinyInteger('amount');
            $table->unsignedTinyInteger('reason');
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
        Schema::dropIfExists('game_points');
    }
}
