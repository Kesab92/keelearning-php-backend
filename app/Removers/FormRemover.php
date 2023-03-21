<?php

namespace App\Removers;

use App\Models\Forms\Form;
use App\Removers\Traits\CourseDependencyMessage;
use App\Services\MorphTypes;

class FormRemover extends Remover
{
    use CourseDependencyMessage;

    protected function deleteDependees()
    {
        /** @var Form $form */
        $form = $this->object;
        $this->object->tags()->detach();
        $this->object->allTranslationRelations()->delete();

        foreach($form->fields as $formField) {
            $formField->safeRemove();
        }

        foreach($form->answers as $formAnswer) {
            $formAnswer->safeRemove();
        }
    }

    /*
     * Checks if anything has this form as dependency
     *
     * @return false if clear of blocking dependees, array of strings if not
     */
    public function getBlockingDependees()
    {
        $messages = [];

        $messages = array_merge($messages, $this->getCourseMessages(MorphTypes::TYPE_FORM, $this->object->id));

        if (count($messages) > 0) {
            return $messages;
        } else {
            return false;
        }
    }

    /**
     * Gets amount of dependees that will be deleted/altered
     *
     * @return boolean|array false if clear of dependees, array of counts if not
     */
    public function getDependees()
    {
        /** @var Form $form */
        $form = $this->object;

        return [
            'Antworten' => $form->answers->count(),
        ];
    }
}
