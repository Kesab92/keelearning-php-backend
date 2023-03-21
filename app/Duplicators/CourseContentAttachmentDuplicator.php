<?php

namespace App\Duplicators;

use App\Duplicators\Duplicator;

class CourseContentAttachmentDuplicator extends Duplicator
{
    protected function keepRelationships(): array
    {
         return [
            'attachment' => [],
        ];
    }

    protected function duplicateRelationships(): array
    {
        if ($this->isCloningAcrossApps()) {
            return [
                'attachment' => [],
            ];
        }
        return [];
    }
}
