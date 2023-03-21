<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Courses\CourseContent;
use App\Models\Courses\CourseParticipation;
use App\Models\Tag;
use App\Services\CourseCertificateRenderer;
use Auth;
use Carbon\Carbon;
use View;

class CoursesController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:courses,courses-edit|courses-view');
        View::share('activeNav', 'courses');
    }

    /**
     * Shows the index page of all courses.
     */
    public function index()
    {
        return view('vue-component', [
            'hasFluidContent' => false,
            'component' => 'routed',
        ]);
    }

    public function show($id)
    {
        $tags = Tag::whereAppId(appId())->get();

        return view('vue-component', [
            'hasFluidContent' => false,
            'component' => 'course',
            'props' => [
                'id' => $id,
                'tags' => $tags,
            ],
        ]);
    }

    public function certificatePreview($contentId)
    {
        $courseContent = CourseContent::findOrFail($contentId);
        if ($courseContent->chapter->course->app_id !== appId()) {
            app()->abort(404);
        }

        $courseParticipation = new CourseParticipation();
        $courseParticipation->id = 0;
        $courseParticipation->user_id = Auth::user()->id;
        $courseParticipation->updated_at = Carbon::now();
        $courseParticipation->course_id = $courseContent->chapter->course_id;
        $renderer = new CourseCertificateRenderer($courseContent, $courseParticipation, null, language());

        return $renderer->render();
    }
}
