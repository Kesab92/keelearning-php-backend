<?php
namespace App\Transformers\BackendApi\Forms;

use App\Models\Forms\FormField;
use App\Services\Forms\FormEngine;
use App\Transformers\AbstractTransformer;

class FormDetailPageTransformer extends AbstractTransformer
{

    public function transform($model)
    {
        if (! $model) {
            return;
        }

        $formFieldTransformer = app(FormFieldTransformer::class);
        $formUsageTransformer = app(FormUsageTransformer::class);
        $formEngine = app(FormEngine::class);

        $fields = $formFieldTransformer
            ->transformAll($model->fields)
            ->sortBy('position')
            ->values()->toArray();

        $usages = $formEngine->getUsages($model);

        return [
            'categories' => $model->categories->pluck('id'),
            'cover_image_url' => $model->cover_image_url,
            'created_at' => $model->created_at,
            'created_by_username' => $model->createdBy->username,
            'fields' => $fields,
            'id' => $model->id,
            'is_archived' => $model->is_archived,
            'is_draft' => $model->is_draft,
            'last_updated_by_username' => $model->lastUpdatedBy->username,
            'tags' => $model->tags->pluck('id'),
            'title' => $model->title,
            'translations' => $model->allTranslationRelations->whereIn('language', [defaultAppLanguage(), language()])->values(),
            'updated_at' => $model->updated_at,
            'usages' => $formUsageTransformer->transformAll($usages),
        ];
    }
}

