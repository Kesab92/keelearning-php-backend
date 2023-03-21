<?php

namespace App\Http\Controllers\PublicApi;

use App\Http\APIError;
use App\Http\Controllers\Controller;
use App\Http\Requests\PublicApi\Tag\TagListFormRequest;
use App\Http\Requests\PublicApi\Tag\TagStoreFormRequest;
use App\Models\Tag;
use App\Transformers\PublicApi\TagTransformer;
use Illuminate\Http\JsonResponse;

class TagsController extends Controller
{
    /**
     * Returns a list of tags.
     *
     * @param TagListFormRequest $request
     * @param TagTransformer $tagTransformer
     * @return JsonResponse
     */
    public function index(TagListFormRequest $request, TagTransformer $tagTransformer):JsonResponse {
        $appId = \Auth::user()->app_id;

        $validated = $request->validated();

        $tags = Tag::ofApp($appId)
            ->whereNull('deleted_at')
            ->offset($validated['perPage'] * $validated['page'])
            ->limit($validated['perPage'])
            ->get();

        return response()->json($tagTransformer->transformAll($tags));
    }

    /**
     * @param TagStoreFormRequest $request
     * @param TagTransformer $tagTransformer
     * @return JsonResponse
     */
    public function store(TagStoreFormRequest $request, TagTransformer $tagTransformer):JsonResponse {
        $appId = \Auth::user()->app_id;

        $validated = $request->validated();

        $tag = new Tag();
        $tag->app_id = $appId;
        $tag->label = $validated['label'];
        $tag->save();

        return response()->json($tagTransformer->transform($tag), 201);
    }

}
