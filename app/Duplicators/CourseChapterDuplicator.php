<?php

namespace App\Duplicators;

use App\Duplicators\Duplicator;

class CourseChapterDuplicator extends Duplicator
{
    protected function duplicateRelationships(): array
    {
        return [
            'contents' => [],
        ];
    }
}
