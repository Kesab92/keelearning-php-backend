<?php

namespace App\Services\Access;

use App\Models\AzureVideo;
use App\Models\LearningMaterial;
use App\Models\LearningMaterialFolder;
use App\Models\User;
use Carbon\Carbon;
use Exception;

class LearningMaterialAccess implements AccessInterface
{
    /**
     * @var array
     */
    private $userTags;
    /**
     * @var User
     */
    private  $user;
    /**
     * @param User $user
     * @param LearningMaterial $resource
     * @return bool
     * @throws Exception
     */
    public function hasAccess(User $user, $resource)
    {
        $this->user = $user;

        if (! $resource instanceof LearningMaterial) {
            throw new Exception('Invalid use of LearningMaterialAccess class');
        }

        if ($resource->published_at > Carbon::now()) {
            return false;
        }

        if (!$resource->visible) {
            return false;
        }

        $this->userTags = $user->tags()->pluck('tags.id')->values()->all();
        $materialTagIds = $resource->tags->pluck('id')->toArray();

        // Check if the user can see the material
        if ($materialTagIds) {
            if (count(array_intersect($materialTagIds, $this->userTags)) === 0) {
                return false;
            }
        }

        $folder = $resource->learningMaterialFolder;

        $hasAccessToFolders = $this->hasAccessToFolder($folder);

        if(!$hasAccessToFolders) {
            return false;
        }

        if ($resource->file_type === 'azure_video') {
            $azureVideo = AzureVideo::where('app_id', $user->app_id)->where('id', $resource->file)->first();
            if (!$azureVideo) {
                return false;
            }
        }

        return true;
    }

    private function hasAccessToFolder($folder) {
        $folderTagIds = $folder->tags->pluck('id')->toArray();

        if($this->user->app_id !== $folder->app_id) {
            return false;
        }

        if($folder->parent_id) {
            $parentFolder = LearningMaterialFolder::findOrFail($folder->parent_id);

            return $this->hasAccessToFolder($parentFolder);
        }

        // Check if the user can see the folder
        if ($folderTagIds) {
            if (count(array_intersect($folderTagIds, $this->userTags)) === 0) {
                return false;
            }
        }

        return true;
    }
}
