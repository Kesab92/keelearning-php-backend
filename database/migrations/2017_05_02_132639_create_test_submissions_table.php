<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestSubmissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('test_submissions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('test_id')
                  ->references('id')
                  ->on('tests')
                  ->index('test_id');
            $table->integer('user_id')
                  ->references('id')
                  ->on('users');
            $table->boolean('result')->nullable();
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
        Schema::dropIfExists('test_submissions');
    }
}
