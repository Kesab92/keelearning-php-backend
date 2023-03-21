<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestAwardTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('test_award_tags', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('test_id')->references('id')->on('tests');
            $table->bigInteger('tag_id')->references('id')->on('tags');
            $table->timestamps();
            $table->index('test_id');
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
        Schema::dropIfExists('test_award_tags');
    }
}
