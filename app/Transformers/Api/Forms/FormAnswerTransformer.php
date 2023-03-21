<?php
namespace App\Transformers\Api\Forms;

use App\Transformers\AbstractTransformer;

class FormAnswerTransformer extends AbstractTransformer
{

    public function transform($model)
    {
        if (! $model) {
            return;
        }

        $formAnswerFieldTransformer = app(FormAnswerFieldTransformer::class);

        return [
            'fields' => $formAnswerFieldTransformer->transformAll($model->fields),
            'id' => $model->id,
        ];
    }
}

