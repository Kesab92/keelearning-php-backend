<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndividualAttendeesColumnsToCourses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->boolean('has_individual_attendees')->default(false)->index();
        });

        Schema::create('course_individual_attendees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id')->references('id')->on('courses')->index();
            $table->unsignedBigInteger('user_id')->references('id')->on('users')->index();
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
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('has_individual_attendees');
        });
        Schema::dropIfExists('course_individual_attendees');
    }
}
