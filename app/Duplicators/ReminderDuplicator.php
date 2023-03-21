<?php

namespace App\Duplicators;

class ReminderDuplicator extends Duplicator
{

    protected function duplicateRelationships(): array
    {
        return [
            'metadata' => [],
        ];
    }
}
