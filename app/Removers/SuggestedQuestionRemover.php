<?php

namespace App\Removers;

  use App\Removers\Remover;

class SuggestedQuestionRemover extends Remover
{
    /*
     * Deletes the answers to the question
     *
     */
    protected function deleteDependees()
    {
        $this->object->questionAnswers()->delete();
    }
}
