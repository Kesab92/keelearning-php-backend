<?php
namespace App\Transformers\PublicApi;

use App\Transformers\AbstractTransformer;

class TagTransformer extends AbstractTransformer
{

    public function transform($model)
    {
        if (! $model) {
            return;
        }
        return [
            'id' => $model->id,
            'label' => $model->label,
        ];
    }
}

