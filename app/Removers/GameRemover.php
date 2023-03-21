<?php

namespace App\Removers;

use App\Removers\Remover;

class GameRemover extends Remover
{
    /**
     * Deletes/Resets everything depending on the game.
     */
    protected function deleteDependees()
    {
        $this->object->gameQuestions()->each(function ($entry) {
            $entry->gameQuestionAnswers()->delete();
        });
        $this->object->gameQuestions()->delete();
        $this->object->gameRounds()->delete();
    }
}
