<?php

namespace App\Removers;

use App\Models\Courses\CourseContentAttempt;
use App\Models\Forms\FormAnswer;
use App\Services\MorphTypes;

/**
 * @property CourseContentAttempt $object
 */
class CourseContentAttemptRemover extends Remover
{
    /**
     * Deletes the course content and its attachments.
     */
    protected function deleteDependees()
    {
        $formAnswers = FormAnswer
            ::where('foreign_type', MorphTypes::TYPE_COURSE_CONTENT_ATTEMPT)
            ->where('foreign_id', $this->object->id)
            ->get();

        foreach($formAnswers as $formAnswer) {
            $formAnswer->safeRemove();
        }
    }

    /**
     * Executes the actual deletion.
     *
     * @return true
     */
    protected function doDeletion()
    {
        $this->deleteDependees();
        $this->object->delete();

        return true;
    }
}
