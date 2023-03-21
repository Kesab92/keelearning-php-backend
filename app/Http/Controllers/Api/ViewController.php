<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LearningMaterial;
use App\Models\News;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ViewController extends Controller
{
    /**
     * Add view to a dashboard/app.
     */
    public function viewHome()
    {
        $user = user();
        $user->app->addView($user);

        return response()->json();
    }

    /**
     * Add view to a learning material.
     * @param Request $request
     * @return JsonResponse|void
     */
    public function viewLearningMaterial(Request $request)
    {
        $learningMaterial = LearningMaterial::findOrFail($request->get('id'));
        if ($learningMaterial->published_at > Carbon::now()) {
            return;
        }
        $folder = $learningMaterial->learningMaterialFolder;
        $folderTagIds = $folder->tags->pluck('id')->toArray();
        $usersTagIds = user()->tags()->pluck('tags.id')->values()->all();
        if ($folderTagIds && ! count(array_intersect($folderTagIds, $usersTagIds))) {
            return;
        }
        $materialTagIds = $learningMaterial->tags->pluck('id')->toArray();
        if ($materialTagIds && ! count(array_intersect($materialTagIds, $usersTagIds))) {
            return;
        }
        $learningMaterial->addView(user());

        return response()->json();
    }

    /**
     * Add view to a news entry.
     * @param Request $request
     * @return JsonResponse
     */
    public function viewNews(Request $request)
    {
        $news = News::visibleToUser(user())->findOrFail($request->get('id'));
        $news->addView(user());

        return response()->json();
    }
}
