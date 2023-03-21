<?php

namespace App\Duplicators;

class TodolistDuplicator extends Duplicator
{
    protected function duplicateRelationships(): array
    {
        return  [
            'todolistItems' => [],
        ];
    }
}
