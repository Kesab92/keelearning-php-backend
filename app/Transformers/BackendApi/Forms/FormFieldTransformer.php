<?php
namespace App\Transformers\BackendApi\Forms;

use App\Transformers\AbstractTransformer;

class FormFieldTransformer extends AbstractTransformer
{

    public function transform($model)
    {
        if (! $model) {
            return;
        }

        return [
            'form_id' => $model->form_id,
            'id' => $model->id,
            'is_required' => $model->is_required,
            'position' => $model->position,
            'title' => $model->title,
            'translations' => $model->allTranslationRelations->values(),
            'type' => $model->type,
        ];
    }
}

