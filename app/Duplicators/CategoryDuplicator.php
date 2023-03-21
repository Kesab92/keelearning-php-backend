<?php

namespace App\Duplicators;

use App\Duplicators\Duplicator;

class CategoryDuplicator extends Duplicator
{
    protected function cloneOnlyOnce(): bool {
        return $this->isCloningAcrossApps() && $this->isChildProcess();
    }

    protected function keepRelationships(): array
    {
        return [
            'categorygroup' => [],
            'tags' => [],
        ];
    }

    protected function duplicateRelationships(): array
    {
        $duplicateRelationships = [
            'hiders' => [],
        ];
        if ($this->isCloningAcrossApps()) {
            $duplicateRelationships['categorygroup'] = [];
        }
        // if we started by cloning the parent categorygroup or this category itself
        if (
            $this->_metadata['root'] == CategoryDuplicator::class
            || $this->_metadata['root'] == CategorygroupDuplicator::class
        ) {
            $duplicateRelationships['allQuestions'] = [];
            $duplicateRelationships['indexCards'] = [];
        }
        return $duplicateRelationships;
    }
}
