<?php

namespace App\Http\Controllers\Backend;

use App\Exports\CourseFormStatistics;
use App\Exports\CourseStatistics;
use App\Exports\CourseTestStatistics;
use App\Exports\CourseWBTStatistics;
use App\Http\Controllers\Controller;
use App\Models\Courses\CourseContent;
use App\Models\Tag;
use App\Services\Courses\CoursesEngine;
use App\Traits\PersonalData;
use Auth;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use View;

class CourseStatisticsController extends Controller
{
    use PersonalData;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:courses,courses-stats');
        $this->personalDataRightsMiddleware('courses');
        View::share('activeNav', 'courses');
    }

    /**
     * Shows the index page of all courses.
     */
    public function index()
    {
        return view('vue-component', [
            'hasFluidContent' => false,
            'component' => 'courses',
            'props' => [
                'tags' => Tag::ofApp(appId())->get(),
            ],
        ]);
    }

    public function show($id)
    {
        return view('vue-component', [
            'hasFluidContent' => false,
            'component' => 'course-statistics',
            'props' => [
                'id' => $id,
                'tags' => Tag::ofApp(appId())->get(),
            ],
        ]);
    }

    public function exportOverall($courseId, CoursesEngine $coursesEngine) {
        $course = $coursesEngine->getCourse($courseId, Auth::user());
        if(!$course) {
            app()->abort(404);
        }
        return Excel::download(new CourseStatistics($course->id, Auth::user(), $this->showPersonalData, $this->showEmails), 'kurs-statistik-' . Str::slug($course->title) . '-' . $course->id . '-' . date('Y-m-d') . '.xlsx');
    }

    public function exportTest($courseId, $contentId, CoursesEngine $coursesEngine) {
        $course = $coursesEngine->getCourse($courseId, Auth::user());
        if(!$course) {
            app()->abort(404);
        }
        $content = $course->contents()->where('course_contents.id', $contentId)->first();
        if(!$content) {
            app()->abort(404);
        }
        return Excel::download(new CourseTestStatistics($course->id, $content->id, Auth::user(), $this->showPersonalData, $this->showEmails), 'kurs-test-statistik-' . Str::slug($content->title) . '-' . $contentId . '-' . Str::slug($course->title) . '-' . $course->id . '-' . date('Y-m-d') . '.xlsx');
    }

    public function exportForm(int $courseId, int $courseContentId, CoursesEngine $coursesEngine) {
        $course = $coursesEngine->getCourse($courseId, Auth::user());
        if(!$course) {
            app()->abort(404);
        }
        $content = $course
            ->contents()
            ->where('course_contents.id', $courseContentId)
            ->where('course_contents.type', CourseContent::TYPE_FORM)
            ->firstOrFail();
        return Excel::download(new CourseFormStatistics($course->id, $content->id, Auth::user(), $this->showPersonalData, $this->showEmails), 'kurs-formular-statistik-' . Str::slug($content->title) . '-' . $courseContentId . '-' . Str::slug($course->title) . '-' . $course->id . '-' . date('Y-m-d') . '.xlsx');
    }

    public function exportWBT($courseId, $contentId, CoursesEngine $coursesEngine) {
        $course = $coursesEngine->getCourse($courseId, Auth::user());
        if(!$course) {
            app()->abort(404);
        }
        $content = $course->contents()->where('course_contents.id', $contentId)->first();
        if(!$content) {
            app()->abort(404);
        }
        return Excel::download(new CourseWBTStatistics($course->id, $content->id, Auth::user(), $this->showPersonalData, $this->showEmails), 'kurs-wbt-statistik-' . Str::slug($content->title) . '-' . $contentId . '-' . Str::slug($course->title) . '-' . $course->id . '-' . date('Y-m-d') . '.xlsx');
    }
}
