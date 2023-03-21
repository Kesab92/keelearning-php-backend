<?php

namespace App\Services\Access;

use App\Models\Competition;
use App\Models\Courses\Course;
use App\Models\Courses\CourseContentAttempt;
use App\Models\LearningMaterial;
use App\Models\News;
use Exception;

class AccessFactory
{
    /**
     * Returns the access checker for the given resource.
     *
     * @param $resource
     * @return AccessInterface
     * @throws Exception
     */
    public static function getAccessChecker($resource) : AccessInterface
    {
        switch ($resource) {
            case $resource instanceof News:
                return new NewsAccess();
            case $resource instanceof Competition:
                return new CompetitionAccess();
            case $resource instanceof Course:
                return new CourseAccess();
            case $resource instanceof CourseContentAttempt:
                return new CourseContentAttemptAccess();
            case $resource instanceof LearningMaterial:
                return new LearningMaterialAccess();
            default:
                throw new Exception('No AccessChecker defined for this resource');
        }
    }
}
