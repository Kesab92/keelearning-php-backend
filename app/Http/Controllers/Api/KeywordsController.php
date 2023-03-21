<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Keywords\Keyword;
use App\Services\Keywords\KeywordEngine;
use Illuminate\Http\Request;
use Response;

class KeywordsController extends Controller
{

    /**
     * Returns keywords data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function keywords()
    {
        $user = user();

        $keywords = Keyword::where('app_id', $user->app_id)
            ->with('translationRelation')
            ->get();

        $keywords->transform(function($keyword) {
            return [
                'id' => $keyword->id,
                'name' => trim($keyword->name),
            ];
        });

        return Response::json(['keywords' => $keywords]);
    }

    /**
     * Returns descriptions of keywords
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function descriptions(Request $request)
    {
        $user = user();

        $keywords = Keyword::where('app_id', $user->app_id)
            ->whereIn('id', explode(',', $request->input('keywords')))
            ->with('translationRelation')
            ->get();

        $keywords->transform(function($keyword) {
            return [
                'id' => $keyword->id,
                'description' => $keyword->description,
            ];
        });

        return Response::json(['keywords' => $keywords]);
    }
}
