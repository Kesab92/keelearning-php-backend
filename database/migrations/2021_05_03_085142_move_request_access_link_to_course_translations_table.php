<?php

use App\Models\Courses\Course;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MoveRequestAccessLinkToCourseTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('course_translations', function (Blueprint $table) {
            $table->string('request_access_link')->nullable()->after('description');
        });
        $courses = Course::whereNotNull('request_access_link')->get();
        foreach ($courses as $course) {
            $translation = $course->translationRelation->first();
            $translation->request_access_link = $course->getOriginal('request_access_link');
            $translation->save();
        }
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('request_access_link');
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
            $table->string('request_access_link')->nullable();
        });
        Schema::table('course_translations', function (Blueprint $table) {
            $table->dropColumn('request_access_link');
        });
    }
}
