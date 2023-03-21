<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMoreFieldsToCourseContentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('course_contents', function (Blueprint $table) {
            $table->boolean('is_test')->nullable()->default(null);
            $table->boolean('is_repeatable')->default(false);
            $table->boolean('show_correct_result')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('course_contents', function (Blueprint $table) {
            $table->dropColumn(['is_test', 'is_repeatable', 'show_correct_result']);
        });
    }
}
