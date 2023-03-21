<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateIndexCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('index_cards', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('app_id')
                  ->references('id')
                  ->on('apps');
            $table->text('front');
            $table->text('back');
            $table->integer('category_id')
                  ->references('id')
                  ->on('categories');
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
        Schema::drop('index_cards');
    }
}
