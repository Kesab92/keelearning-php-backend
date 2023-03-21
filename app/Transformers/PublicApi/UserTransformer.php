<?php
namespace App\Transformers\PublicApi;

use App\Transformers\AbstractTransformer;

class UserTransformer extends AbstractTransformer
{

    public function transform($model)
    {
        if (! $model) {
            return;
        }

        $tagTransformer = app(TagTransformer::class);

        $response = [
            'id' => $model->id,
            'username' => $model->username,
            'firstname' => $model->firstname,
            'lastname' => $model->lastname,
            'email' => $model->email,
            'language' => $model->language,
            'active' => $model->active,
            'tags' => $tagTransformer->transformAll($model->tags),
            'created_at' => $model->created_at->toIso8601ZuluString(),
        ];

        if($model->app->getUserMetaDataFields(true)) {
            $response['meta'] = $model->getMeta();
        }

        return $response;
    }
}
