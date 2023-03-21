<?php
namespace App\Transformers\BackendApi\Courses;

use App\Transformers\AbstractTransformer;

class SimpleCourseContentWithSimpleCourseTransformer extends AbstractTransformer
{

    public function transform($model)
    {
        if (! $model) {
            return;
        }

        $simpleCourseTransformer = app(SimpleCourseTransformer::class);

        return [
            'course' => $simpleCourseTransformer->transform($model->course),
            'id' => $model->id,
            'title' => $model->title,
        ];
    }
}

