<?php

namespace App\Duplicators;

use App\Duplicators\Duplicator;

class LearningMaterialTranslationDuplicator extends Duplicator
{
    protected function duplicateRelationships(): array
    {
        if ($this->isCloningAcrossApps() && $this->original->file_type == 'azure_video') {
            return [
                'azureVideo' => [],
            ];
        }
        return [];
    }
}
