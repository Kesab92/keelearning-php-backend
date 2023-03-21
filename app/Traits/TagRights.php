<?php

namespace App\Traits;

use App\Models\Tag;
use App\Models\User;
use Auth;
use Exception;

trait TagRights
{
    /**
     * Adds an scope for tag rights. The model should only queried if user has no user_tag_rights or specific one.
     * @param $query - Builds the db query.
     * @param User|null $admin
     * @return mixed
     */
    public function scopeTagRights($query, ?User $admin = null)
    {
        if($admin === null) {
            $admin = Auth::user();
        }

        if ($admin->isFullAdmin()) {
            return $query;
        }

        $tagIds = $admin->tagRightsRelation->pluck('id');
        return $query->whereHas('tags', function ($tagQuery) use ($tagIds) {
            return $tagQuery->whereIn('tags.id', $tagIds);
        });
    }

    /**
     * Checks if a given admin user can access this resource.
     * @param User|null $admin
     * @return mixed
     */
    public function isAccessibleByAdmin(?User $admin = null)
    {
        if($admin === null) {
            $admin = Auth::user();
        }
        if ($admin->isFullAdmin() || !$this->tags->count()) {
            return true;
        }
        return $this->tags->pluck('id')
            ->intersect($admin->tagRightsRelation->pluck('id'))
            ->isNotEmpty();
    }

    /**
     * Sync tags having tags restriction
     * @param array $newTags
     * @param string $relation
     * @param User|null $admin
     * @param boolean $allowEmptyTags allows TAG-restricted admin to remove the last TAG they can see,
     *                                effectively rendering the resource inaccessible to them in some instances
     * @return array
     * @throws Exception
     */
    public function syncTags($newTags, $relation = 'tags', $allowEmptyTags = false)
    {
        $admin = Auth::user();
        $tags = Tag::where('app_id', appId())
            ->whereIn('id', $newTags)
            ->pluck('id');

        if (!$admin->isFullAdmin()) {
            $adminTags = $admin->tagRightsRelation->pluck('id');
            $tags = $tags->intersect($adminTags)->values();
            if (!$tags->count() && !$allowEmptyTags) {
                app()->abort(403, 'Sie müssen mindestens einen TAG vergeben!');
            }

            $tags = $this->{$relation}->pluck('id')
                ->filter(function ($existingTag) use ($adminTags) {
                    // we keep all existing TAGs that the admin can't edit
                    return !$adminTags->contains($existingTag);
                })
                ->merge($tags)
                ->unique();
        }

        return $this->{$relation}()->sync($tags);
    }
}
