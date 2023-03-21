<?php
namespace App\Transformers\BackendApi\Forms;

use App\Transformers\AbstractTransformer;

class FormListTransformer extends AbstractTransformer
{

    public function transform($model)
    {
        if (! $model) {
            return;
        }
        return [
            'categories' => $model->categories->pluck('id'),
            'id' => $model->id,
            'is_archived' => $model->is_archived,
            'is_draft' => $model->is_draft,
            'tags' => $model->tags->pluck('id'),
            'title' => $model->title,
        ];
    }
}

