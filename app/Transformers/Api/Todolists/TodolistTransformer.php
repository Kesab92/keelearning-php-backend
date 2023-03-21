<?php

namespace App\Transformers\Api\Todolists;

use App\Transformers\AbstractTransformer;

class TodolistTransformer extends AbstractTransformer
{

    /**
     * @inheritDoc
     */
    public function transform($model)
    {
        $items = app(TodolistItemTransformer::class)
            ->transformAll($model->todolistItems)
            ->sortBy('position')
            ->values()
            ->toArray();
        return [
            'id' => $model->id,
            'foreign_id' => $model->foreign_id,
            'foreign_type' => $model->foreign_type,
            'items' => $items,
        ];
    }
}
