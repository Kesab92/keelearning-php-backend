<?php

namespace App\Removers;

class ReminderRemover extends Remover
{
    protected function deleteDependees()
    {
        $this->object->metadata()->delete();
    }
}
