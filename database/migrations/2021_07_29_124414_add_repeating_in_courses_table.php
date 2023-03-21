<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRepeatingInCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->boolean('is_repeating')->default(false)->index();
            $table->integer('repetition_interval');
            $table->integer('repetition_interval_type');
            $table->integer('repetition_count');
            $table->integer('time_limit');
            $table->integer('time_limit_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('is_repeating');
            $table->dropColumn('repetition_interval');
            $table->dropColumn('repetition_interval_type');
            $table->dropColumn('repetition_count');
            $table->dropColumn('time_limit');
            $table->dropColumn('time_limit_type');
        });
    }
}
