<?php

namespace App\Transformers\Api\Todolists;

class TodolistItemTransformer extends \App\Transformers\AbstractTransformer
{

    /**
     * @inheritDoc
     */
    public function transform($model)
    {
        return [
            'id' => $model->id,
            'description' => $model->description,
            'position' => $model->position,
            'title' => $model->title,
        ];
    }
}
