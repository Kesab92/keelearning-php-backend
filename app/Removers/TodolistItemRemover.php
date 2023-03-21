<?php

namespace App\Removers;

use App\Models\TodolistItem;

/**
 * @property TodolistItem $object
 */
class TodolistItemRemover extends Remover
{
    public function getDependees()
    {
        $answerCount = $this->object->answers()->count();
        if(!$answerCount) {
            return false;
        }
        return [
            'Aufgabenstatus von Usern' => $answerCount,
        ];
    }

    protected function deleteDependees()
    {
        $this->object->answers()->delete();
        $this->object->deleteAllTranslations();
    }

    /**
     * Executes the actual deletion.
     *
     * @return true
     */
    protected function doDeletion()
    {
        $this->deleteDependees();
        $this->object->forceDelete();

        return true;
    }
}
