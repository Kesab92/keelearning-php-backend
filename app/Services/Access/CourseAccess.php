<?php

namespace App\Services\Access;

use App\Models\Courses\Course;
use App\Models\User;
use App\Services\Courses\CoursesEngine;
use Exception;

class CourseAccess implements AccessInterface
{
    /**
     * @param User $user
     * @param Course $resource
     * @return bool
     * @throws Exception
     */
    public function hasAccess(User $user, $resource)
    {
        if (! $resource instanceof Course) {
            throw new Exception('Invalid use of CourseAccess class');
        }

        /** @var CoursesEngine $coursesEngine */
        $coursesEngine = app(CoursesEngine::class);

        return (bool) $coursesEngine->getUsersCourse($user, $resource->id);
    }
}
