<?php

namespace App\Removers;

use App\Models\Forms\FormAnswerField;
use App\Models\Forms\FormField;

class FormFieldRemover extends Remover
{
    protected function deleteDependees()
    {
        /** @var FormField $formField */
        $formField = $this->object;
        $this->object->allTranslationRelations()->delete();
        $formField->answers()->delete();
    }

    /**
     * Gets amount of dependees that will be deleted/altered
     *
     * @return boolean|array false if clear of dependees, array of counts if not
     */
    public function getDependees()
    {
        /** @var FormField $formField */
        $formField = $this->object;

        return [
            'Antworten' => $formField->answers->count(),
        ];
    }
}
