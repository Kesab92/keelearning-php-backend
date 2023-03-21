<?php

namespace App\Duplicators;

use App\Models\ContentCategories\ContentCategory;
use Exception;

class CourseDuplicator extends Duplicator
{
    protected function setProperties(): array
    {
        $properties = [
            'creator_id' => null,
            'visible' => 0,
        ];

        if ($this->isCloningAcrossApps()) {
            $properties['preview_enabled'] = 0;
        }

        return $properties;
    }

    protected function keepRelationships(): array
    {
        return [
            'categories' => [
                'pivotValues' => [
                    'type' => ContentCategory::TYPE_COURSES,
                ],
            ],
            'managers' => [],
            'previewTags' => [],
            'tags' => [],
            'awardTags' => [],
            'retractTags' => [],
        ];
    }

    protected function duplicateRelationships(): array
    {
        return [
            'chapters' => [],
            'reminders' => [],
        ];
    }

    protected function validateCloningProcess(): void
    {
        parent::validateCloningProcess();
        if ($this->original->archived_at) {
            throw new Exception('Cannot clone archived course!');
        }
    }
}
