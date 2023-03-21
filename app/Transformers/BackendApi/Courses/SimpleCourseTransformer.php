<?php
namespace App\Transformers\BackendApi\Courses;

use App\Transformers\AbstractTransformer;

class SimpleCourseTransformer extends AbstractTransformer
{

    public function transform($model)
    {
        if (! $model) {
            return;
        }

        return [
            'id' => $model->id,
            'tags' => $model->tags->pluck('id'),
            'title' => $model->title,
        ];
    }
}

