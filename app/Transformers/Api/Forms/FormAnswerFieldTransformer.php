<?php
namespace App\Transformers\Api\Forms;

use App\Transformers\AbstractTransformer;

class FormAnswerFieldTransformer extends AbstractTransformer
{

    public function transform($model)
    {
        if (! $model) {
            return;
        }

        return [
            'answer' => $model->answer,
            'id' => $model->id,
            'form_field_id' => $model->form_field_id,
        ];
    }
}

