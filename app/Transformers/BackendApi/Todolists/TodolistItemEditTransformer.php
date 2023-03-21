<?php
namespace App\Transformers\BackendApi\Todolists;

use App\Models\Forms\FormField;
use App\Models\TodolistItem;
use App\Services\Forms\FormEngine;
use App\Transformers\AbstractTransformer;

class TodolistItemEditTransformer extends AbstractTransformer
{

    public function transform($model)
    {
        /** @var TodolistItem $model */
        if (! $model) {
            return;
        }

        return [
            'id' => $model->id,
            'position' => $model->position,
            'title' => $model->title,
            'description' => $model->description,
            'translations' => $model->allTranslationRelations->whereIn('language', [defaultAppLanguage(), language()])->values(),
        ];
    }
}

