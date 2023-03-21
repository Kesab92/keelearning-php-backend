<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategorygroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categorygroups', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('app_id')->references('id')->on('apps');
            $table->string('name');

            $table->timestamps();
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->integer('categorygroup_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('categorygroups');
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('categorygroup_id');
        });
    }
}
