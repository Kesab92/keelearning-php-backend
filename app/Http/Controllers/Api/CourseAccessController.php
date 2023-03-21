<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Courses\CoursesEngine;
use Illuminate\Http\JsonResponse;
use Response;

class CourseAccessController extends Controller
{
    /**
     * Requests access to a course
     *
     * @param $courseId
     * @param CoursesEngine $coursesEngine
     * @return JsonResponse
     */
    public function requestAccess($courseId, CoursesEngine $coursesEngine)
    {
        $user = user();
        $course = $coursesEngine->getUsersCourse($user, $courseId);
        if (! $course) {
            app()->abort(404);
        }

        $coursesEngine->requestAccess($course, $user);

        return Response::json([]);
    }
}
