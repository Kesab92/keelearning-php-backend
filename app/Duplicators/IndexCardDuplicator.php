<?php

namespace App\Duplicators;

class IndexCardDuplicator extends Duplicator
{

    protected function keepRelationships(): array
    {
        return [
            'category' => [],
        ];
    }
}
