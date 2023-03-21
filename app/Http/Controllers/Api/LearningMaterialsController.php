<?php

namespace App\Http\Controllers\Api;

use App\Http\APIError;
use App\Http\Controllers\Controller;
use App\Models\LearningMaterial;
use App\Models\Like;
use App\Services\Access\AccessFactory;
use App\Services\CommentEngine;
use App\Services\LearningMaterialEngine;
use App\Services\LikesEngine;
use App\Services\WbtEngine;
use Illuminate\Http\JsonResponse;
use Response;

class LearningMaterialsController extends Controller
{
    public function show($id, LearningMaterialEngine $learningMaterialEngine, LikesEngine $likesEngine)
    {
        $learningMaterial = LearningMaterial::findOrFail($id);

        $user = user();

        $accessChecker = AccessFactory::getAccessChecker($learningMaterial);
        if (!$accessChecker->hasAccess($user, $learningMaterial)) {
            abort(403);
        }

        $learningMaterial->published_at = $learningMaterial->hasPublishedAtDate()
            ? $learningMaterial->published_at : $learningMaterial->created_at;

        $likeCount = $likesEngine->likesCount(Like::TYPE_LEARNINGMATERIAL, $learningMaterial->id);

        LearningMaterial::attachLastViewedAt(collect([$learningMaterial]), $user);
        $learningMaterial = $learningMaterial->append('watermark')->toArray();
        $learningMaterial = $learningMaterialEngine->formatMaterialData($learningMaterial, $user, $likeCount);

        return Response::json($learningMaterial);
    }

    public function list(LearningMaterialEngine $learningMaterialEngine, CommentEngine $commentEngine)
    {
        $user = user();
        $folders = $learningMaterialEngine->getUsersFolders($user);
        $allMaterials = collect([]);

        foreach ($folders as $folder) {
            $materials = $this->getMaterialsCollection($folder['materials']);
            $allMaterials = $allMaterials->merge($materials);
        }
        $commentCount = $commentEngine->getCommentsCount($allMaterials, $user);

        $apiVersion = request()->header('X-API-VERSION', '1.0.0');
        if(version_compare($apiVersion, '3.1.0', '<')) {
            $folders = array_map(function($folder) use ($commentCount, $commentEngine, $user) {
                $folder['materials'] = array_map(function($material) use ($commentCount, $folder) {
                    $material['learning_material_folder'] = $folder;
                    $material['comment_count'] = $commentCount->get($material['id'], 0);
                    unset($material['learning_material_folder']['materials']);
                    return $material;
                }, $folder['materials']);
                return $folder;
            }, $folders);
        } else {
            $folders = array_map(function($folder) use ($commentCount, $user) {
                $folder['materials'] = array_map(function($material) use ($commentCount, $folder) {
                    $material['comment_count'] = $commentCount->get($material['id'], 0);
                    return $material;
                }, $folder['materials']);
                return $folder;
            }, $folders);
        }

        return Response::json(array_values($folders));
    }

    /**
     * Returns all wbt events of the given learning material for the user.
     *
     * @param $material_id
     *
     * @return JsonResponse|APIError
     */
    public function wbtEvents($material_id)
    {
        if(!user()->app->hasXAPI()) {
            return Response::json([
                'events' => [],
            ]);
        }
        $wbtEngine = new WbtEngine(user()->app_id);
        $learningMaterial = LearningMaterial::whereHas('learningMaterialFolder', function ($query) {
            $query->where('app_id', user()->app_id);
        })->findOrFail($material_id);
        $events = $wbtEngine->getUserLearningMaterialEvents(user(), $learningMaterial);

        return Response::json([
            'events' => $events->values(),
        ]);
    }

    /**
     * Returns all wbt events for the user.
     *
     * @return JsonResponse|APIError
     */
    public function allWbtEvents()
    {
        $user = user();
        if(!$user->app->hasXAPI()) {
            return Response::json([
                'events' => [],
            ]);
        }
        $wbtEngine = new WbtEngine($user->app_id);
        $events = $wbtEngine->getUserEvents($user);

        return Response::json([
            'events' => $events->values(),
        ]);
    }

    private function getMaterialsCollection($materialsArray) {
        $materials = collect();
        foreach($materialsArray as $materialItem) {
            $material = new LearningMaterial();
            $material->id = $materialItem['id'];
            $materials->push($material);
        }

        return $materials;
    }
}
