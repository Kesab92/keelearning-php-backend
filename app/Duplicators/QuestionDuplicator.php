<?php

namespace App\Duplicators;

use App\Duplicators\Duplicator;

class QuestionDuplicator extends Duplicator
{
    protected function cloneOnlyOnce(): bool {
        return $this->isCloningAcrossApps() && $this->isChildProcess();
    }

    protected function setProperties(): array
    {
        $properties = [
            'creator_user_id' => null,
        ];

        if (!$this->isCloningAcrossApps()) {
            $properties['visible'] = 0;
        }

        return $properties;
    }

    protected function keepRelationships(): array
    {
        return [
            'category' => [],
        ];
    }

    protected function duplicateRelationships(): array
    {
        $duplicateRelationships =  [
            'attachments' => [],
            'questionAnswers' => [],
        ];

        if (
            $this->isCloningAcrossApps()
            && $this->_metadata['root'] != CategoryDuplicator::class
            && $this->_metadata['root'] != CategorygroupDuplicator::class
        ) {
            $duplicateRelationships['category'] = [];
        }

        return $duplicateRelationships;
    }
}
