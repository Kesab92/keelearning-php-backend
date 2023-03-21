<?php

namespace App\Duplicators;

class LearningMaterialFolderDuplicator extends Duplicator
{
    protected function cloneOnlyOnce(): bool
    {
        return $this->isCloningAcrossApps();
    }

    protected function keepRelationships(): array
    {
        return [
            'parentFolder' => [],
            'tags' => [],
        ];
    }

    protected function duplicateRelationships(): array
    {
        $relationships = [];
        if ($this->isCloningAcrossApps()) {
            $relationships['parentFolder'] = [];
        }
        // If we started by cloning a LearningMaterialFolder,
        // we're cloning in a top-to-bottom direction and
        // want the contents too.
        // For things like courses and LearningMaterials,
        // we only want the raw parent folders without content.
        // Also: If we're currently cloning a parent folder we don't want to clone its contents
        // because we're only interested in the folder itself.
        if ($this->_metadata['root'] == LearningMaterialFolderDuplicator::class && !$this->isCloningParentDependency()) {
            $relationships['childFolders'] = [];
            $relationships['learningMaterials'] = [];
        }
        return $relationships;
    }

    public function isParentDependency(string $relationship): bool
    {
        return $relationship === 'parentFolder';
    }
}
