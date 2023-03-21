<?php

namespace App\Removers;

use App\Models\Forms\FormAnswer;

class FormAnswerRemover extends Remover
{
    protected function deleteDependees()
    {
        /** @var FormAnswer $formAnswer */
        $formAnswer = $this->object;
        $formAnswer->fields()->delete();
    }
}
