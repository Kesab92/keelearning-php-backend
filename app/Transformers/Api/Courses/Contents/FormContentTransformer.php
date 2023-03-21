<?php
namespace App\Transformers\Api\Courses\Contents;

use App\Models\Forms\Form;
use App\Transformers\AbstractTransformer;
use App\Transformers\Api\Forms\FormFieldTransformer;

class FormContentTransformer extends AbstractTransformer
{

    public function transform($model)
    {
        if (! $model) {
            return;
        }
        /** @var Form $form */
        $form = $model->relatable;

        $formFieldTransformer = app(FormFieldTransformer::class);

        $fields = $formFieldTransformer
            ->transformAll($form->fields)
            ->sortBy('position')
            ->values()->toArray();

        return [
            'cover_image_url' => $form->cover_image_url,
            'fields' => $fields,
            'id' => $form->id,
            'title' => $model->title ?: $form->title,
        ];
    }
}

