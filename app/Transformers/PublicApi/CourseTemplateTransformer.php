<?php
namespace App\Transformers\PublicApi;

use App\Transformers\AbstractTransformer;

class CourseTemplateTransformer extends AbstractTransformer
{

    public function transform($courseTemplate)
    {
        if (! $courseTemplate) {
            return;
        }


        $startsAt = null;

        if($courseTemplate->available_from) {
            $startsAt = $courseTemplate->available_from->toIso8601ZuluString();
        }

        return [
            'id' => $courseTemplate->id,
            'title' => $courseTemplate->title,
            'is_active' => !!$courseTemplate->visible,
            'is_mandatory' => !!$courseTemplate->is_mandatory,
            'created_at' => $courseTemplate->created_at->toIso8601ZuluString(),
            'starts_at' => $startsAt,
            'max_duration' => $courseTemplate->getCourseMaxDuration(),
            'category_ids' => $courseTemplate->categories()->allRelatedIds(),
        ];
    }
}
