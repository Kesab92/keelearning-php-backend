<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Models\AzureVideo;
use App\Models\Courses\Course;
use App\Models\LearningMaterial;
use App\Models\LearningMaterialFolder;
use App\Models\Tag;
use App\Services\AppSettings;
use App\Services\AzureVideo\AzureVideoEngine;
use App\Services\ImageUploader;
use App\Services\LearningMaterialEngine;
use App\Services\LikesEngine;
use Illuminate\Http\File;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Response;
use Sopamo\LaravelFilepond\Filepond;
use Storage;

class LearningMaterialsController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:learningmaterials,learningmaterials-edit');
    }

    /**
     * @return JsonResponse
     */
    public function index(LikesEngine $likesEngine)
    {
        $folders = LearningMaterialFolder
            ::where('app_id', appId())
            ->with([
                'allTranslationRelations',
                'cloneRecord',
                'tags',
                'translationRelation',
            ])
            ->select(['id', 'folder_icon_url', 'parent_id', 'created_at'])
            ->get()
            ->transform(function(LearningMaterialFolder $folder) {
                $data = $folder->only([
                    'id',
                    'folder_icon_url',
                    'parent_id',
                    'created_at',
                    'is_reusable_clone',
                ]);
                if($data['id'] === $data['parent_id']) {
                    \Sentry::captureMessage('Folder ' . $data['id'] . ' has itself as parent!');
                }
                $data['translations'] = $folder->allTranslationRelations->whereIn('language', [defaultAppLanguage(), language()])->values();
                foreach($folder->translated as $translationField) {
                    $data[$translationField] = $folder->getAttribute($translationField);
                }
                $data['tags'] = $folder->tags->map(function ($tag) {
                    return $tag->id;
                });
                $folder->unsetRelation('translationRelation');
                $folder->unsetRelation('tags');
                return $data;
            })
            ->keyBy('id');

        $materials = LearningMaterial
            ::whereIn('learning_material_folder_id', $folders->pluck('id'))
            ->with([
                'allTranslationRelations',
                'learningMaterialFolder',
                'tags',
                'translationRelation',
            ])
            ->get();

        $likesCounts = $likesEngine->getLikesCounts($materials);

        LearningMaterial::attachViewcountTotals($materials);

        $materials = $materials->transform(function(LearningMaterial $material) use ($likesCounts) {
            $data = $material->only([
                'id',
                'file',
                'learning_material_folder_id',
                'published_at',
                'notification_sent_at',
                'cover_image_url',
                'send_notification',
                'viewcount_total',
                'visible',
                'created_at',
            ]);
            $data['translations'] = $material->allTranslationRelations->whereIn('language', [defaultAppLanguage(), language()])->values();
            $translation = $material->allTranslationRelations->where('language', language())->first();
            $defaultTranslation = $material->allTranslationRelations->where('language', defaultAppLanguage())->first();
            foreach($material->translated as $translationField) {
                if($translation) {
                    $data[$translationField] = $translation->getAttribute($translationField);
                } else {
                    $data[$translationField] = '';
                }
                if(!$data[$translationField] && $defaultTranslation) {
                    $data[$translationField] = $defaultTranslation->getAttribute($translationField);
                }
            }
            $data['tags'] = $material->tags->map(function ($tag) {
                return $tag->id;
            });
            $material->unsetRelation('tags');

            if($likesCounts->has($material->id)) {
                $data['likes'] = $likesCounts->get($material->id);
            } else {
                $data['likes'] = 0;
            }

            return $data;
        })
        ->keyBy('id');

        return Response::json([
            'folders' => $folders,
            'materials' => $materials,
        ]);
    }

    private function getLearningmaterial($learningmaterialId)
    {
        $material = LearningMaterial::findOrFail($learningmaterialId);
        if($material->learningMaterialFolder->app_id !== appId()) {
            app()->abort(404);
        }
        return $material;
    }

    private function getMaterialResponse(LearningMaterial $material) {
        $likesEngine = new LikesEngine();
        $learningMaterialEngine = app(LearningMaterialEngine::class);

        $material->append('is_reusable_clone');
        $material->load('tags');
        $material->tags->transform(function($tag) {
            return $tag->id;
        });
        $material->translations = $material->allTranslationRelations->whereIn('language', [defaultAppLanguage(), language()])->values();
        $material->unsetRelation('allTranslationRelations');
        LearningMaterial::attachViewcountTotals(collect([$material]));

        $likesCounts = $likesEngine->getLikesCounts(collect([$material]));
        $material['likes'] = $likesCounts->get($material['id']) ?: 0;

        $usageCourseIds = $learningMaterialEngine->getUsages($material);
        $courses = Course
            ::whereIn('id', $usageCourseIds)
            ->with(['translationRelation'])
            ->get();

        $material['usages'] = $courses->map->only(['id', 'title']);

        if ($material->isAzureVideo()) {
            $material['subtitles_language'] = AzureVideo::find($material->file)->subtitles_language;
        }

        return [
            'material' => $material,
        ];
    }

    public function show($learningmaterialId) {
        $material = $this->getLearningmaterial($learningmaterialId);
        return Response::json($this->getMaterialResponse($material));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|min:1|max:255',
            'folder_id' => ['required', Rule::exists('App\Models\LearningMaterialFolder', 'id')->where(function ($query) {
                $query->where('app_id', appId());
            })],
        ]);

        $learningMaterial = new LearningMaterial();
        $learningMaterial->learning_material_folder_id = $request->input('folder_id');
        $learningMaterial->setLanguage(defaultAppLanguage(appId()));
        $learningMaterial->title = $request->input('title');
        $learningMaterial->save();

        return Response::json($this->getMaterialResponse($learningMaterial));
    }

    public function update($learningmaterialId, Request $request)
    {
        $material = $this->getLearningmaterial($learningmaterialId);
        $basicFields = [
            'description',
            'download_disabled',
            'link',
            'published_at',
            'show_watermark',
            'title',
            'visible',
            'wbt_subtype',
        ];
        foreach($basicFields as $field) {
            $value = $request->input($field, null);
            if($value !== null) {
                $material->setAttribute($field, $value);
            }
        }
        if($request->input('learning_material_folder_id', null) !== null) {
            $folder = LearningMaterialFolder::find($request->input('learning_material_folder_id'));
            if(!$folder || $folder->app_id !== appId()) {
                app()->abort(403, 'Sie dürfen in diesen Ordner keine Dateien verschieben.');
            }
            $material->learning_material_folder_id = $folder->id;
        }
        if($request->input('cover_image_url', null) !== null) {
            $material->cover_image_url = $request->input('cover_image_url');
            $material->cover_image = formatAssetURL($material->cover_image_url, '1.0.0');
        }
        $material->save();

        if($request->input('tags', null) !== null) {
            $newTags = Tag::where('app_id', appId())
                ->whereIn('id', $request->input('tags'))
                ->pluck('id')
                ->toArray();
            $material->tags()->sync($newTags);
        }

        if ($material->isAzureVideo() && $request->has('subtitles_language')) {
            $azureVideo = AzureVideo::find($material->file);
            app(AzureVideoEngine::class)->setSubtitleLanguage($azureVideo, $request->input('subtitles_language'));
        }

        return Response::json($this->getMaterialResponse($material));
    }

    /**
     * Clones the learning material.
     * @param int $learningMaterialId
     * @return JsonResponse
     */
    public function clone(int $learningMaterialId): JsonResponse
    {
        $newLearningMaterial = $this->getLearningmaterial($learningMaterialId)->duplicate();
        $newLearningMaterial->title = 'Kopie von ' . $newLearningMaterial->title;
        $newLearningMaterial->save();

        return Response::json([
            'learning_material_id' => $newLearningMaterial->id,
        ]);
    }


    /**
     * Sets the media for a learning material.
     *
     * @param $learningmaterialId
     * @param Request $request
     * @param AzureVideoEngine $azureVideoEngine
     * @param LearningMaterialEngine $learningMaterialEngine
     */
    public function upload($learningmaterialId, Request $request, AzureVideoEngine $azureVideoEngine, LearningMaterialEngine $learningMaterialEngine)
    {
        set_time_limit(0);
        $learningMaterial = $this->getLearningmaterial($learningmaterialId);

        $filepond = app(Filepond::class);
        $filePath = $filepond->getPathFromServerId($request->input('serverId'));
        $temporaryDisk = config('filepond.temporary_files_disk');
        $readStream = Storage::disk($temporaryDisk)->readStream($filePath);
        $filename = $request->input('filename');
        $mimeType = $request->input('fileType');
        $extension = $request->input('fileExtension');

        // .mpp MIME type is not detected by symfony
        if ($extension == 'mpp') {
            $mimeType = 'application/vnd.ms-office';
        }

        if (! LearningMaterial::isValidFileType($filePath, $mimeType, $extension) && ! $azureVideoEngine->isAVideo($mimeType)) {
            app()->abort(400);
        }

        $translation = $learningMaterial->translation(null, false);
        if($translation) {
            $learningMaterialEngine->removeMedia($translation);
        }

        $learningMaterial->file_size_kb = Storage::disk($temporaryDisk)->size($filePath) / 1024;

        if ($azureVideoEngine->isAVideo($mimeType)) {
            $tmpfname = tempnam("/tmp", uniqid('video'));
            file_put_contents($tmpfname, $readStream);
            Storage::disk($temporaryDisk)->delete($filePath);
            $tmpFile = new File($tmpfname);
            $azureVideo = $azureVideoEngine->uploadVideo(appId(), $tmpFile);
            $learningMaterial->file_type = 'azure_video';
            $learningMaterial->file = $azureVideo->id;
            $learningMaterial->file_url = '';
        } elseif (isZipFile($mimeType, $extension)) {
            if (! app(AppSettings::class)->getValue('wbt_enabled')) {
                app()->abort(400);
            }
            $tmpfname = tempnam("/tmp", uniqid('wbt'));
            file_put_contents($tmpfname, $readStream);
            Storage::disk($temporaryDisk)->delete($filePath);
            $tmpFile = new File($tmpfname);
            $learningMaterialEngine->uploadWbt($learningMaterial, $tmpFile, $filename);
        } else {
            $newLocation = 'uploads/' . createFilenameFromString($filename);
            $readStream = Storage::disk($temporaryDisk)->readStream($filePath);
            Storage::writeStream($newLocation, $readStream, [
                'mimetype' => $mimeType,
            ]);
            Storage::disk($temporaryDisk)->delete($filePath);
            $learningMaterial->file_type = $mimeType;
            $learningMaterial->file = $newLocation;
            $learningMaterial->file_url = Storage::url($newLocation);
        }

        $learningMaterial->save();
    }


    public function notify($learningmaterialId, AppSettings $appSettings, LearningMaterialEngine $learningMaterialEngine)
    {
        $material = $this->getLearningmaterial($learningmaterialId);
        if (! $material || $material->notification_sent_at) {
            return Response::json([
                'success' => false,
            ]);
        }

        if (! $material->published_at || $material->published_at->isPast()) {
            $learningMaterialEngine->sendNotification($material);
        } else {
            $material->send_notification = true;
            $material->save();
        }

        return Response::json($this->getMaterialResponse($material));
    }

    public function reset($learningmaterialId, LearningMaterialEngine $learningMaterialEngine)
    {
        $material = $this->getLearningmaterial($learningmaterialId);
        $learningMaterialEngine->removeMedia($material->translation());

        return Response::json($this->getMaterialResponse($material));
    }

    /**
     * Sets the cover image for a learning material.
     *
     * @param Request $request
     * @param $id
     * @param ImageUploader $imageUploader
     * @return JsonResponse
     */
    public function uploadCoverImage($learningmaterialId, Request $request, ImageUploader $imageUploader)
    {
        $material = $this->getLearningmaterial($learningmaterialId);

        $file = $request->file('file');
        if (! $imageUploader->validate($file)) {
            app()->abort(403);
        }

        if (!$imagePath = $imageUploader->upload($file, 'uploads/learningmaterial-cover')) {
            app()->abort(400);
        }
        $imagePath = formatAssetURL($imagePath, '3.0.0');

        return \Response::json([
            'image' => $imagePath,
        ]);
    }

    public function deleteInformation($learningmaterialId)
    {
        $material = $this->getLearningmaterial($learningmaterialId);
        return Response::json([
            'dependencies' => $material->safeRemoveDependees(),
            'blockers' => $material->getBlockingDependees(),
        ]);
    }

    public function delete($learningmaterialId) {
        $material = $this->getLearningmaterial($learningmaterialId);

        $result = $material->safeRemove();

        if($result->isSuccessful()) {
            return Response::json([], 204);
        } else {
            return Response::json($result->getMessages(), 400);
        }
    }
}
