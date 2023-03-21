<?php

namespace App\Removers;

use App\Removers\Traits\CourseDependencyMessage;
use App\Services\MorphTypes;

class AppointmentRemover extends Remover
{
    use CourseDependencyMessage;

    protected function deleteDependees()
    {
        $this->object->tags()->detach();
        $this->object->allTranslationRelations()->delete();
    }

    /*
     * Checks if anything has this appointment as dependency
     *
     * @return false if clear of blocking dependees, array of strings if not
     */
    public function getBlockingDependees()
    {
        $messages = [];

        $messages = array_merge($messages, $this->getCourseMessages(MorphTypes::TYPE_APPOINTMENT, $this->object->id));

        if (count($messages) > 0) {
            return $messages;
        } else {
            return false;
        }
    }
}
