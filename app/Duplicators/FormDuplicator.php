<?php

namespace App\Duplicators;

class FormDuplicator extends Duplicator
{

    protected function duplicateRelationships(): array
    {
        return [
            'fields' => [],
        ];
    }
}
