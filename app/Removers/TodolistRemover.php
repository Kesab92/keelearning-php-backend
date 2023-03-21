<?php

namespace App\Removers;

use App\Models\Todolist;
use App\Models\TodolistItem;

/**
 * @property Todolist $object
 */
class TodolistRemover extends Remover
{
    protected function deleteDependees()
    {
        $this->object->todolistItems()->each(function(TodolistItem $item) {
            $item->safeRemove();
        });
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
