<?php
namespace App\Transformers\BackendApi\Forms;

use App\Transformers\AbstractTransformer;

class SimpleFormFieldTransformer extends AbstractTransformer
{

    public function transform($model)
    {
        if (! $model) {
            return;
        }

        return [
            'title' => $model->title,
            'type' => $model->type,
        ];
    }
}

