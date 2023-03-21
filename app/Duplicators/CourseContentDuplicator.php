<?php

namespace App\Duplicators;

use App\Duplicators\Duplicator;
use App\Models\Courses\CourseContent;
use App\Services\MorphTypes;

class CourseContentDuplicator extends Duplicator
{
    protected function keepRelationships(): array
    {
        $keepRelationships = [
            'tags' => [],
        ];

        /*
         * Learning materials should be reusable in the same app
         * because of disc space-saving Besides, admins don't want
         * a lot of duplicated files in the media library.
         */
        if (!$this->isCloningAcrossApps() && $this->original->type === CourseContent::TYPE_LEARNINGMATERIAL) {
            $keepRelationships['relatable'] = [];
        }

        return $keepRelationships;
    }

    protected function duplicateRelationships(): array
    {
        $duplicateRelationships = [
            'attachments' => [],
        ];

        if ($this->isCloningAcrossApps()) {
            // questions are as attachments, so we don't have to clone "relatable"
            if ($this->original->type !== MorphTypes::TYPE_COURSE_CONTENT_QUESTIONS) {
                $duplicateRelationships['relatable'] = [];
            }
        } else {
            switch ($this->original->type) {
                case CourseContent::TYPE_CERTIFICATE:
                    /*
                     * Certificates always have to be cloned, because there is no way to edit
                     * a specific certificate independently of the course, so if we would reuse
                     * a certificate, it would change in all courses if it were to be edited in
                     * one of the courses.
                     */
                    $duplicateRelationships['relatable'] = [];
                    break;
                case CourseContent::TYPE_TODOLIST:
                    /*
                     * Todolists always have to be cloned, because currently there is no way to create/manage them outside
                     * of courses, so they are specific to a single course content.
                     */
                    $duplicateRelationships['relatable'] = [];
                    break;
            }
        }

        return $duplicateRelationships;
    }
}
