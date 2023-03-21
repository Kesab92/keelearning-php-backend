<?php
namespace App\Transformers\BackendApi\Forms;

use App\Transformers\AbstractTransformer;

class SimpleFormTransformer extends AbstractTransformer
{

    public function transform($model)
    {
        if (! $model) {
            return;
        }

        $simpleFormFieldTransformer = app(SimpleFormFieldTransformer::class);

        $fields = $simpleFormFieldTransformer
            ->transformAll($model->fields->sortBy('position'))
            ->values()->toArray();

        return [
            'fields' => $fields,
            'id' => $model->id,
            'is_archived' => $model->is_archived,
            'is_draft' => $model->is_draft,
            'title' => $model->title,
        ];
    }
}

