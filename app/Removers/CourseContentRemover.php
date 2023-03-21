<?php

namespace App\Removers;

use App\Models\Courses\CourseContent;
use App\Models\Todolist;

/**
 * @property CourseContent $object
 */
class CourseContentRemover extends Remover
{
    /**
     * Deletes the course content and its attachments.
     */
    protected function deleteDependees()
    {
        $this->object->attachments()->delete();
        $this->object->deleteAllTranslations();

        $todolist = Todolist::where('foreign_type', Todolist::TYPE_COURSE_CONTENT)
            ->where('foreign_id', $this->object->id)
            ->first();
        if($todolist) {
            $todolist->safeRemove();
        }

        foreach($this->object->attempts as $attempt) {
            $attempt->safeRemove();
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
