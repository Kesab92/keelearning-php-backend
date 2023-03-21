<?php

namespace App\Duplicators;

use App\Duplicators\Duplicator;

// this is currently used for bottom-up cloning,
// starting at the level of a single category
class CategorygroupDuplicator extends Duplicator
{
    protected function cloneOnlyOnce(): bool {
        return $this->isCloningAcrossApps() && $this->isChildProcess();
    }

    protected function keepRelationships(): array
    {
        return [
            'tags' => [],
        ];
    }
}
