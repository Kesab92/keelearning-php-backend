<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddParticipationDurationFieldsToCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->smallInteger('duration_type')->default(0);
            $table->integer('participation_duration')->default(2);
            $table->smallInteger('participation_duration_type')->default(1);
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
            $table->dropColumn('duration_type');
            $table->dropColumn('participation_duration');
            $table->dropColumn('participation_duration_type');
        });
    }
}
