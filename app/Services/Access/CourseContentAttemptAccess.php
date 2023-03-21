<?php

namespace App\Services\Access;

use App\Models\Courses\CourseContentAttempt;
use App\Models\User;
use App\Services\Courses\CoursesEngine;
use Exception;

class CourseContentAttemptAccess implements AccessInterface
{
    /**
     * @param User $user
     * @param CourseContentAttempt $resource
     * @return bool
     * @throws Exception
     */
    public function hasAccess(User $user, $resource)
    {
        if (! $resource instanceof CourseContentAttempt) {
            throw new Exception('Invalid use of CourseContentAttemptAccess class');
        }

        if ($resource->participation->user_id !== $user->id) {
            return false;
        }

        $course = $resource->content->course;

        /** @var CoursesEngine $coursesEngine */
        $coursesEngine = app(CoursesEngine::class);
        if(!$coursesEngine->getUsersCourse($user, $course->id)) {
            return false;
        }

        return (bool) $coursesEngine->getCourseContent($course, $resource->course_content_id, $user);
    }
}
