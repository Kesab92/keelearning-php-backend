<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeRepetitionCountInCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->integer('repetition_count')->nullable(true)->change();
            $table->integer('time_limit')->nullable(true)->change();
            $table->integer('repetition_interval')->nullable(true)->change();
            $table->integer('repetition_interval_type')->nullable(true)->change();
            $table->integer('time_limit_type')->nullable(true)->change();
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
            $table->integer('repetition_count')->nullable(false)->change();
            $table->integer('time_limit')->nullable(false)->change();
            $table->integer('repetition_interval')->nullable(false)->change();
            $table->integer('repetition_interval_type')->nullable(false)->change();
            $table->integer('time_limit_type')->nullable(false)->change();
        });
    }
}
