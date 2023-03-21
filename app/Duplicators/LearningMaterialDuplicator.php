<?php

namespace App\Duplicators;

use App\Duplicators\Duplicator;

class LearningMaterialDuplicator extends Duplicator
{
    protected function setProperties(): array
    {
        $properties = [];
        if (
            ($this->isCloningAcrossApps() && $this->isChildProcess())
            || !$this->isChildProcess()
        ) {
            $properties['visible'] = false;
        }
        return $properties;
    }

    protected function cloneOnlyOnce(): bool {
        return $this->isCloningAcrossApps() && $this->isChildProcess();
    }

    protected function keepRelationships(): array
    {
        return [
            'tags' => [],
        ];
    }

    protected function duplicateRelationships(): array
    {
        if ($this->isCloningAcrossApps() && $this->_metadata['root'] != LearningMaterialFolderDuplicator::class) {
            return [
                'learningMaterialFolder' => [],
            ];
        }
        return [];
    }
}
