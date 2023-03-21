<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Models\LearningMaterial;
use App\Models\LearningMaterialFolder;
use App\Models\Tag;
use App\Services\ImageUploader;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Response;

class LearningMaterialFoldersController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:learningmaterials,learningmaterials-edit');
    }

    public function update($folderId, Request $request)
    {
        $folder = $this->getFolder($folderId);
        $basicFields = ['name'];
        foreach ($basicFields as $field) {
            $value = $request->input($field, null);
            if ($value !== null) {
                $folder->setAttribute($field, $value);
            }
        }
        if($request->has('parent_id')) {
            if($request->input('parent_id') !== null) {
                $parentFolder = LearningMaterialFolder::find($request->input('parent_id'));
                if(!$parentFolder || $parentFolder->app_id !== appId() || $parentFolder->id == $folder->id) {
                    app()->abort(403, 'Sie dürfen in diesen Ordner keine Dateien verschieben.');
                }
            }
            $folder->parent_id = $request->input('parent_id');
        }
        if ($request->input('folder_icon_url', null) !== null) {
            $folder->folder_icon_url = $request->input('folder_icon_url');
            $folder->folder_icon = formatAssetURL($folder->folder_icon_url, '1.0.0');
        }
        $folder->save();

        if ($request->input('tags', null) !== null) {
            $newTags = Tag::where('app_id', appId())
                ->whereIn('id', $request->input('tags'))
                ->pluck('id')
                ->toArray();
            $folder->tags()->sync($newTags);
        }

        return Response::json($this->getFolderResponse($folder));
    }

    private function getFolder($folderId)
    {
        $folder = LearningMaterialFolder::findOrFail($folderId);
        if ($folder->app_id !== appId()) {
            app()->abort(404);
        }
        return $folder;
    }

    private function getFolderResponse(LearningMaterialFolder $folder)
    {
        $folder->load('tags');
        $folder->tags->transform(function ($tag) {
            return $tag->id;
        });
        $folder->translations = $folder->allTranslationRelations->whereIn('language', [defaultAppLanguage(), language()])->values();
        $folder->unsetRelation('allTranslationRelations');
        return [
            'folder' => $folder,
        ];
    }

    /**
     * Store a new folder
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|min:1|max:255',
            'parent_id' => [
                'nullable',
                Rule::exists('App\Models\LearningMaterialFolder','id')->where(function ($query) {
                    $query->where('app_id', appId());
                }),
            ],
        ]);
        $learningMaterialFolder = new LearningMaterialFolder();
        $learningMaterialFolder->setLanguage(defaultAppLanguage(appId()));
        $learningMaterialFolder->name = $request->input('name');
        $learningMaterialFolder->parent_id = $request->input('parent_id', null);
        $learningMaterialFolder->app_id = appId();
        $learningMaterialFolder->save();

        return Response::json($this->getFolderResponse($learningMaterialFolder));
    }

    private function getMaterialResponse(LearningMaterial $material)
    {
        $material->load('tags');
        $material->tags->transform(function ($tag) {
            return $tag->id;
        });
        return [
            'material' => $material,
        ];
    }

    public function deleteInformation($folderId)
    {
        $folder = $this->getFolder($folderId);
        return Response::json([
            'dependencies' => $folder->safeRemoveDependees(),
            'blockers' => $folder->getBlockingDependees(),
        ]);
    }

    public function delete($folderId) {
        $folder = $this->getFolder($folderId);

        $result = $folder->safeRemove();

        if($result->isSuccessful()) {
            return Response::json([], 204);
        } else {
            return Response::json($result->getMessages(), 400);
        }
    }
}
