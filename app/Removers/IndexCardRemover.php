<?php

namespace App\Removers;

use App\Models\LearnBoxCard;

class IndexCardRemover extends Remover
{
    /**
     * Deletes the learning progress for the indexcards.
     *
     * @throws \Exception
     */
    protected function deleteDependees()
    {
        LearnBoxCard::where('type', LearnBoxCard::TYPE_INDEX_CARD)
            ->where('foreign_id', $this->object->id)
            ->delete();
    }

    /**
     * Executes the actual deletion.
     *
     * @return true
     * @throws \Exception
     */
    protected function doDeletion()
    {
        $this->deleteDependees();
        $this->object->delete();

        return true;
    }
}
