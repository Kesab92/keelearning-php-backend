<?php
namespace App\Transformers\BackendApi\CourseStatistics;

use App\Models\Forms\FormField;
use App\Transformers\AbstractTransformer;

class FormFieldTransformer extends AbstractTransformer
{

    public function transform($model)
    {
        if (! $model) {
            return;
        }

        return [
            'id' => $model->id,
            'position' => $model->position,
            'title' => $model->getFormattedTitle(),
            'type' => $model->type,
        ];
    }
}

