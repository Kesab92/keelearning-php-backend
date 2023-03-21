<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('test_categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->integer('test_id')
                  ->references('id')
                  ->on('tests');
            $table->integer('category_id')
                  ->references('id')
                  ->on('categories');
            $table->integer('question_amount');

            $table->index('test_id');
            $table->index('category_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('test_categories');
    }
}
