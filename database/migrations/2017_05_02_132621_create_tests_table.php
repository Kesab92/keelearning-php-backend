<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tests', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('app_id')
                  ->references('id')
                  ->on('apps')
                  ->index('app_id');
            $table->string('name');
            $table->integer('min_rate');
            $table->integer('group_id')->nullable()->references('id')->on('groups');
            $table->integer('tag_id')->nullable()->references('id')->on('groups');
            $table->boolean('repeatable');
            $table->dateTime('active_until');
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
        Schema::dropIfExists('tests');
    }
}
