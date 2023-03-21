<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class ChangeTestTags extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     * @throws Throwable
     */
    public function up()
    {
        DB::transaction(function () {
            $tests = App\Models\Test::all();

            \Schema::table('tests', function (Blueprint $table) {
                $table->dropColumn('tag_id');
            });

            \Schema::create('test_tags', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('test_id')->references('id')->on('tests');
                $table->integer('tag_id')->references('id')->on('tags');
                $table->timestamps();
            });

            foreach ($tests as $test) {
                $test->tags()->sync($test->tag_id);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::transaction(function () {
            \Schema::dropIfExists('test_tags');
            \Schema::table('tests', function (Blueprint $table) {
                $table
                    ->integer('tag_id')
                    ->references('id')
                    ->on('tags');
            });
        });
    }
}
