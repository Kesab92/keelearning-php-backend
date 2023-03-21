<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLearnBoxCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('learn_box_cards', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')
                  ->references('id')
                  ->on('users');
            $table->integer('foreign_id');
            $table->integer('type');
            $table->integer('box');
            $table->text('userdata');
            $table->dateTime('box_entered_at');
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
        Schema::drop('learn_box_cards');
    }
}
