<?php

namespace App\Services;

use App\Jobs\LearningMaterialsPublished;
use App\Models\AzureVideo;
use App\Models\Courses\CourseChapter;
use App\Models\Courses\CourseContent;
use App\Models\LearningMaterial;
use App\Models\LearningMaterialFolder;
use App\Models\LearningMaterialTranslation;
use App\Models\User;
use App\Services\AzureVideo\AzureVideoEngine;
use Cache;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\File;
use Illuminate\Support\Carbon;
use Log;
use Storage;
use ZipArchive;

class LearningMaterialEngine
{
    // further down -> higher priority
    const WBT_ENTRY_POINTS = [
        'index_TINCAN.html',
        'story.html',
        'story_html5.html',
        'index_lms.html',
    ];

    private \Illuminate\Support\Collection $fetchedAzureVideos;
    private \Illuminate\Support\Collection $fetchedAzureVideoActiveSubtitles;

    public function __construct()
    {
        $this->fetchedAzureVideos = collect([]);
        $this->fetchedAzureVideoActiveSubtitles = collect([]);
    }

    public function sendNotification($learningMaterial)
    {
        LearningMaterialsPublished::dispatch($learningMaterial);
        $learningMaterial->notification_sent_at = Carbon::now();
        $learningMaterial->save();
    }

    /**
     * Deletes and unlinks media from the given translation
     */
    public function removeMedia(LearningMaterialTranslation $materialTranslation)
    {
        $file = $materialTranslation->file;
        // We unlink the media before deleting it, because that way we are not dependent on the file deletion
        // being successful to be able to reach the state that the admin wants
        $this->unlinkMedia($materialTranslation);
        $this->deleteMedia($materialTranslation->learningMaterial, $file);
    }

    /**
     * Deletes all media from the given translation
     */
    private function deleteMedia(LearningMaterial $learningMaterial, $file)
    {
        if (!$file) {
            return;
        }
        if (LearningMaterialTranslation::where('file', $file)->exists()) {
            // only delete the file if this is the last instance of it
            return;
        }

        if ($learningMaterial->isWBT(true)) {
            Storage::deleteDirectory($file);
        } elseif ($learningMaterial->isAzureVideo()) {
            // TODO: Delete Video
        } else {
            Storage::delete($file);
        }
    }

    /**
     * Removes the association with all uploaded media files from the learning material translation db entry
     */
    private function unlinkMedia(LearningMaterialTranslation $materialTranslation) {
        $materialTranslation->file = '';
        $materialTranslation->file_url = '';
        $materialTranslation->file_type = '';
        $materialTranslation->file_size_kb = null;
        $materialTranslation->link = '';
        $materialTranslation->wbt_id = null;
        $materialTranslation->save();
    }

    /**
     * Uploads wbt from given zip folder to learning material.
     *
     * @param $learningMaterial
     * @param \Symfony\Component\HttpFoundation\File\File $zipFile
     */
    public function uploadWbt($learningMaterial, $zipFile, $fileName)
    {
        $destinationPath = 'uploads/' . createFilenameFromString($fileName, true);
        $tempPath = storage_path('/tmp/' . uniqid());
        // unzip wbt, which is basically a static web page
        $archive = new ZipArchive();
        if (!$archive->open($zipFile->getRealPath())) {
            return;
        }
        if (!file_exists($tempPath)) {
            mkdir($tempPath);
        }
        $archive->extractTo($tempPath);
        $archive->close();
        // if there's only a folder in the zip, move it upwards one level
        $files = collect(scandir($tempPath))
            ->filter(function ($file) {
                // ispringlane wbts only have a "tincan.xml" file in the root and the real wbt is in the "res" folder
                if ($file === 'tincan.xml') {
                    return false;
                }

                return $file != '.' && $file != '..';
            });
        if ($files->count() === 1) {
            $folderName = $tempPath . '/' . $files->first();
            collect(scandir($tempPath . '/' . $files->first()))
                ->filter(function ($file) {
                    return $file != '.' && $file != '..';
                })
                ->each(function ($zipContent) use ($folderName, $tempPath) {
                    rename($folderName . '/' . $zipContent, $tempPath . '/' . $zipContent);
                });
            rmdir($folderName);
        }
        // if there's no index.html, check the alternative names
        foreach (self::WBT_ENTRY_POINTS as $entryPoint) {
            if (file_exists($tempPath . '/' . $entryPoint)) {
                if (file_exists($tempPath . '/index.html')) {
                    unlink($tempPath . '/index.html');
                }
                copy($tempPath . '/' . $entryPoint, $tempPath . '/index.html');
            }
        }
        $learningMaterial->wbt_custom_entrypoint = $this->getCustomEntrypoint($tempPath);

        // fallback
        $learningMaterial->wbt_id = env('APP_URL') . '/learning-materials/' . $learningMaterial->id;
        // WBT: rise360
        // if we have a tincan.xml, try to parse the ID
        if (file_exists($tempPath . '/tincan.xml')) {
            try {
                $xml = simplexml_load_file($tempPath . '/tincan.xml');
                if ($xml) {
                    $learningMaterial->wbt_id = $xml->activities->activity->attributes()['id']->__toString() . '/learning-materials/' . $learningMaterial->id;
                }
            } catch (\Exception $e) {
                Log::error($e->__toString());
                \Sentry::captureException($e);
            }
        }

        // recursively upload wbt folder
        $this->uploadFolder($destinationPath, $tempPath);

        $learningMaterial->file_type = 'wbt';
        $learningMaterial->file = $destinationPath;
        $learningMaterial->file_url = Storage::url($destinationPath);
        $learningMaterial->wbt_subtype = LearningMaterialTranslation::WBT_SUBTYPE_XAPI;
    }

    /**
     * Try to fetch the entrypoint for SCORM WBTs
     *
     * @param string $tempPath
     * @return string|null
     */
    private function getCustomEntrypoint(string $tempPath)
    {
        if(!file_exists($tempPath . '/imsmanifest.xml')) {
            return null;
        }
        try {
            $xml = simplexml_load_file($tempPath . '/imsmanifest.xml');
            if ($xml) {
                try {
                    return $xml->resources->resource->attributes()['href']->__toString();
                } catch(\Exception $e) {
                    logger($e->__toString());
                    return null;
                }
            }
        } catch (\Exception $e) {
            Log::error($e->__toString());
            \Sentry::captureException($e);
        }
    }

    private function uploadFolder($destination, $source)
    {
        Storage::makeDirectory($destination);
        $contents = scandir($source);
        foreach ($contents as $path) {
            if ($path == '.' || $path == '..') {
                continue;
            }
            $fullPath = $source . '/' . $path;
            if (is_dir($fullPath)) {
                $this->uploadFolder($destination . '/' . $path, $fullPath);
            } else {
                Storage::putFileAs($destination, new File($fullPath), $path);
            }
        }
    }

    public function removeLearningMaterialFolderIcon(LearningMaterialFolder $learningMaterialFolder) {
        $folderIcon = $learningMaterialFolder->folder_icon;
        $learningMaterialFolder->folder_icon = null;
        $learningMaterialFolder->folder_icon_url = null;
        $learningMaterialFolder->save();
        $this->deleteLearningMaterialFolderIcon($folderIcon);
    }

    public function deleteLearningMaterialFolderIcon($folderIcon)
    {
        if (!$folderIcon) {
            return;
        }
        if (LearningMaterialFolder::where('folder_icon', $folderIcon)->exists()) {
            // Only delete this file if this is the last instance of it
            return;
        }
        Storage::delete($folderIcon);
    }

    /**
     * Invalidates the cache tags for the given learning material change.
     *
     * @param int $appId
     * @param array $tagsBefore
     * @param array $tagsAfter
     */
    public function invalidateCache($appId, $tagsBefore = [], $tagsAfter = [], $parentTags = [])
    {
        Cache::tags('learningmaterials-app-' . $appId)->flush();
        if ((!$tagsBefore || !$tagsAfter) && $parentTags) {
            $tagIds = array_map(function ($tag) {
                return 'learningmaterials-tag-' . $tag;
            }, $parentTags);
        } else {
            $tagIds = array_map(function ($tag) {
                return 'learningmaterials-tag-' . $tag;
            }, array_unique(array_merge($tagsBefore, $tagsAfter)));
        }
        Cache::tags($tagIds)->flush();
    }

    public function getUsersFolders(User $user)
    {
        $likesEngine = app(LikesEngine::class);

        $usersTags = $user->tags()->pluck('tags.id')->values()->all();
        $allFolders = LearningMaterialFolder
            ::where('app_id', $user->app_id)
            ->with(['tags', 'translationRelation'])
            ->get()
            ->keyBy('id');

        $folders = $allFolders->filter(function ($folder) use ($usersTags, $allFolders) {
            return $this->folderIsVisible($folder, $allFolders, $usersTags);
        });

        $materials = LearningMaterial
            ::whereIn('learning_material_folder_id', $folders->pluck('id'))
            ->where('visible', true)
            ->with(['tags', 'translationRelation'])
            ->get();

        if(language($user->app_id) !== defaultAppLanguage($user->app_id)) {
            $materials->load('defaultTranslationRelation');
            $folders->load('defaultTranslationRelation');
        }

        LearningMaterial::attachLastViewedAt($materials, $user);

        $likesCount = $likesEngine->getLikesCounts($materials);
        $userLikes = $likesEngine->getUserLikes($materials, $user);

        $sortAlphabetically = false;
        $appSettings = app(AppSettings::class);
        if ($appSettings && $appSettings->getValue('sort_learning_materials_alphabetically')) {
            $sortAlphabetically = true;
        }

        // It's necessary to avoid n+1 problems in fetchAzureVideos()
        foreach($materials as $material) {
            $material->setAppId($user->app_id);
        }

        $this->fetchAzureVideos($materials, $user);

        // Set the materials
        $folders->each(function ($folder) use ($userLikes, $likesCount, $usersTags, $user, $materials, $sortAlphabetically) {
            $folder->materials = $materials
                ->where('learning_material_folder_id', $folder->id)
                ->filter(function ($learningMaterial) use ($usersTags) {
                    if ($learningMaterial->published_at > \Carbon\Carbon::now()) {
                        return false;
                    }
                    return $this->isVisible($learningMaterial, $usersTags);
                })->map(function (LearningMaterial $learningMaterial) use ($user) {
                    $learningMaterial->published_at = $learningMaterial->hasPublishedAtDate()
                        ? $learningMaterial->published_at : $learningMaterial->created_at;
                    return $learningMaterial;
                })->sort(function ($a, $b) use ($sortAlphabetically) {
                    if ($sortAlphabetically) {
                        return strtolower($a->title) > strtolower($b->title);
                    }

                    return $a->published_at < $b->published_at;
                });
            $folder->materials = array_values($folder->materials->append('watermark')->toArray());
            $folder->materials = array_map(function ($material) use ($userLikes, $likesCount, $user) {
                return $this->formatMaterialData($material, $user, $likesCount->get($material['id'], 0), $userLikes->contains($material['id']));
            }, $folder->materials);

            // Filter materials without streaming url
            $folder->materials = array_values(array_filter($folder->materials, function ($material) {
                if ($material['file_type'] === 'azure_video' && !$material['file_url']) {
                    return false;
                }

                return true;
            }));
        });

        $foldersByParentId = $folders->groupBy('parent_id');

        $folders = $folders
            ->filter(function (LearningMaterialFolder $folder) use ($foldersByParentId) {
                // Remove data we don't need in the response
                $folder->unsetRelation('tags');

                // Return only folders with at least one visible material or a visible subfolder
                return $this->folderHasContents($folder, $foldersByParentId);
            })
            ->sort(function ($a, $b) {
                return strtolower($a->name) > strtolower($b->name);
            })
            ->toArray();
        $folders = array_map(function ($folder) {
            unset($folder['translation_relation']);
            return $folder;
        }, $folders);

        return $folders;
    }

    private function folderHasContents(LearningMaterialFolder $folder, \Illuminate\Support\Collection $foldersByParentId) {
        if(count($folder->materials) > 0) {
            return true;
        }
        foreach($foldersByParentId->get($folder->id, []) as $childFolder) {
            if($this->folderHasContents($childFolder, $foldersByParentId)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checks if a given folder is visible for the users tags
     *
     * @param LearningMaterialFolder $folder
     * @param Collection $folders
     * @param $usersTags
     * @return bool
     */
    private function folderIsVisible(LearningMaterialFolder $folder, Collection $folders, $usersTags)
    {
        if ($folder->parent_id) {
            $parent = $folders->get($folder->parent_id);
            if (!$parent) {
                return false;
            }
            if (!$this->folderIsVisible($parent, $folders, $usersTags)) {
                return false;
            }
        }
        return $this->isVisible($folder, $usersTags);
    }

    /**
     * Checks if a given model is visible for the users tags
     * It's important that the model has a preloaded "tags" relation
     *
     * @param $model
     * @param $usersTags
     * @return bool
     */
    private function isVisible($model, $usersTags)
    {
        $modelTagIds = $model->tags->pluck('id')->toArray();

        // Entries without tags are visible to everyone
        if (!$modelTagIds) {
            return true;
        }
        // Return true if the folder has at least one of the user's tags
        return count(array_intersect($modelTagIds, $usersTags)) > 0;
    }

    public function formatMaterialData($material, $user, $likeCount, $likesIt = false)
    {
        $material['cover_image'] = formatAssetURL($material['cover_image']);
        $material['cover_image_url'] = formatAssetURL($material['cover_image_url']);
        if ($material['file_type'] !== 'azure_video') {
            $material['file'] = formatAssetURL($material['file']);
        }
        if ($material['file_type'] !== 'wbt' && $material['file_type'] !== 'azure_video') {
            $material['file_url'] = formatAssetURL($material['file_url']);
        }
        if ($material['file_type'] === 'azure_video') {
            $azureVideo = null;
            $activeSubtitles = collect([]);

            if($this->fetchedAzureVideos->isNotEmpty()) {
                $azureVideo = $this->fetchedAzureVideos->get($material['file']);
                $activeSubtitles =  $this->fetchedAzureVideoActiveSubtitles->get($azureVideo->output_asset_id);
            }

            if(!$azureVideo) {
                $azureVideoEngine = app(AzureVideoEngine::class);
                $azureVideo = AzureVideo::where('app_id', $user->app_id)->where('id', $material['file'])->first();
                $activeSubtitles =  $azureVideoEngine->getActiveSubtitles([$azureVideo->id]);
            }

            $material['file_url'] = '';
            $material['subtitles'] = [];
            if ($azureVideo) {
                $material['file_url'] = $azureVideo->streaming_url;
                if($activeSubtitles) {
                    $material['subtitles'] = $activeSubtitles->map(function ($activeSubtitle) {
                        return [
                            'language' => $activeSubtitle->language,
                            'streaming_url' => $activeSubtitle->streaming_url,
                        ];
                    });
                }
            }
        }
        unset($material['tags']);
        unset($material['translation_relation']);

        $material['likes_count'] = $likeCount;
        $material['likes_it'] = $likesIt;

        return $material;
    }

    public function getUsages(LearningMaterial $learningMaterial) {
        return CourseChapter
            ::select('course_chapters.*')
            ->leftJoin('course_contents', 'course_chapters.id', '=', 'course_contents.course_chapter_id')
            ->where('course_contents.type', CourseContent::TYPE_LEARNINGMATERIAL)
            ->where('course_contents.foreign_id', $learningMaterial->id)
            ->get()
            ->pluck('course_id')
            ->unique();

    }

    private function fetchAzureVideos(Collection $materials, User $user) {
        $azureVideoIds = $materials->filter(function (LearningMaterial $material) {
            return $material->file_type === 'azure_video';
        })->map(function (LearningMaterial $material) {
            return $material->file;
        });

        $this->fetchedAzureVideos = AzureVideo::where('app_id', $user->app_id)
            ->whereIn('id', $azureVideoIds)
            ->get()
            ->keyBy('id');

        $azureVideoEngine = app(AzureVideoEngine::class);

        $this->fetchedAzureVideoActiveSubtitles = $azureVideoEngine->getActiveSubtitles($azureVideoIds->values()->toArray())
            ->groupBy('azure_video_output_asset_id');
    }
}
