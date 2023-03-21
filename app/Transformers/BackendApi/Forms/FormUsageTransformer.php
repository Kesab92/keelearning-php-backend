<?php
namespace App\Transformers\BackendApi\Forms;

use App\Transformers\AbstractTransformer;

class FormUsageTransformer extends AbstractTransformer
{

    public function transform($model)
    {
        if (! $model) {
            return;
        }

        return [
            'id' => $model->id,
            'title' => $model->title,
        ];
    }
}

