<?php
namespace App\Transformers\BackendApi\CourseStatistics;

use App\Models\Forms\FormField;
use App\Transformers\AbstractTransformer;

class FormAnswerFieldTransformer extends AbstractTransformer
{

    public function transform($model)
    {
        if (! $model) {
            return;
        }

        return [
            'answer' => $model->getFormattedAnswer(),
            'form_field_id' => $model->form_field_id,
            'id' => $model->id,
            'position' => $model->formField->position,
        ];
    }
}

