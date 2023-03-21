<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCompetitionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('competitions', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('app_id')
                  ->references('id')
                  ->on('apps');

            $table->integer('category_id')
                  ->references('id')
                  ->on('categories');

            $table->integer('group_id')
                  ->references('id')
                  ->on('groups');

            $table->integer('duration');

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
        Schema::drop('competitions');
    }
}
