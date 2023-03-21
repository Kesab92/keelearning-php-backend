<?php
namespace App\Transformers\Api\Forms;

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
            'is_required' => $model->is_required,
            'position' => $model->position,
            'title' => $model->title,
            'type' => $model->type,
        ];
    }
}

