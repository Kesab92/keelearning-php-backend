<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRepetitionsToCourseContentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('course_contents', function (Blueprint $table) {
            $table->dropColumn('is_repeatable');
            $table->integer('repetitions')->nullable()->default(1);
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
            $table->dropColumn('repetitions');
            $table->boolean('is_repeatable')->default(false);
        });
    }
}
