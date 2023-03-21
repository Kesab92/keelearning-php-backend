<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Tag;
use App\Models\TagGroup;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class PermissionEngine
{
    /**
     * Synchronizes tags which are allowed. If no ones are allowed, every tag is allowed to link with the model.
     * @param Model $model
     * @param array $tagIds
     * @param Collection $allowedTags
     */
    public function syncTags(Model $model, $tagIds, Collection $allowedTags)
    {
        if ($allowedTags->count() == 0) {
            $model->tags()->sync($tagIds);

            return;
        }

        $allowedTags = $this->findAllowedTags(collect($tagIds), $allowedTags);
        $model->tags()->sync($allowedTags);
    }

    /**
     * Returns the tags which are allowed to add.
     * @param $tags
     * @param $allowedTags
     * @return Collection
     */
    public function findAllowedTags(Collection $tags, Collection $allowedTags)
    {
        // If the user has specific TAGs they can edit, that have to select at least one TAG
        $tags = $tags->filter();
        $allowedTags = $allowedTags->filter();
        if ($allowedTags->count() > 0 && $tags->count() === 0) {
            app()->abort(403, 'Sie müssen mindestens einen TAG auswählen.');
        }

        return collect($tags)
            ->filter(function ($item) use ($allowedTags) {
                if (! in_array($item, $allowedTags->toArray())) {
                    app()->abort(403);
                }

                return true;
            });
    }

    /**
     * If the user tag rights array has at least one of the model tags.
     * @param $userTagRights
     * @param $modelTags
     * @return bool
     */
    public function isAllowedToUse($userTagRights, $modelTags)
    {
        return collect($userTagRights)->filter(function ($userTagRightId) use ($modelTags) {
            return $modelTags->contains($userTagRightId);
        })->count() > 0;
    }

    /**
     * Filters already cached players by allowed Tags.
     * @param User $currentUser
     * @param $players
     * @return Collection
     */
    public function filterPlayerStatsByTag(User $currentUser, $players)
    {
        if ($currentUser->tagRightsRelation->count() > 0) {
            $tagIds = $currentUser->tagRightsRelation->pluck('id');
            $players = $players->filter(function ($player) use ($tagIds) {
                // Filter tags which the admin can't see
                $player->tags = $player->tags->filter(function ($tag) use ($tagIds) {
                    return $tagIds->contains($tag->id);
                });

                return $player->tags
                        ->whereIn('id', $tagIds)
                        ->count() > 0;
            });
        }

        return $players;
    }

    /**
     * Filters given questions by user and his tag rights.
     * @param User $user
     * @param $questions
     * @return mixed
     */
    public function filterQuestionStatsByTag(User $user, $questions)
    {
        if ($user->tagRightsRelation->count() > 0) {
            $tagIds = $user->tagRightsRelation->pluck('id');
            $categories = Category::with('tags')
                ->get()
                ->keyBy('id');

            $questions = $questions->filter(function ($question) use ($categories, $tagIds) {
                if ($question->category_id > 0) {
                    $category = $categories[$question->category_id];
                    $category->tags = $category->tags->filter(function ($tag) use ($tagIds) {
                        return $tagIds->contains($tag->id);
                    });

                    return $category->tags
                            ->whereIn('id', $tagIds)
                            ->count() > 0;
                }
            });
        }

        return $questions;
    }

    /**
     * Filters given categories by user and his tag rights.
     * @param User $user
     * @param $categories
     * @return mixed
     */
    public function filterCategoryStatsByTag(User $user, $categories)
    {
        if ($user->tagRightsRelation->count() > 0) {
            $tagIds = $user->tagRightsRelation->pluck('id');
            $categories = $categories->filter(function ($category) use ($tagIds) {
                $category->tags = $category->tags->filter(function ($tag) use ($tagIds) {
                    return $tagIds->contains($tag->id);
                });

                return $category->tags
                        ->whereIn('id', $tagIds)
                        ->count() > 0;
            });
        }

        return $categories;
    }

    /**
     * Returns a collection of TAGs which the admin user has administrative privileges for.
     *
     * @param int $appId
     * @param ?User $user
     * @return Tag[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getAvailableTags($appId, User $user = null)
    {
        $tags = Tag::ofApp($appId)
            ->orderBy('tags.label');

        if($user) {
            $tags = $tags->rights($user);
        }

        return $tags->get();
    }

    /**
     * Returns a collection of TAG Groups which contain at least one TAG the admin user has administrative privileges for.
     *
     * @param int $appId
     * @param User|null $user
     * @return TagGroup[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getAvailableTagGroups($appId, User $user = null)
    {
        $availableTags = $this->getAvailableTags($appId, $user)->pluck('id');

        return TagGroup::ofApp($appId)
            ->select(\DB::raw('tag_groups.*'))
            ->join('tags', 'tags.tag_group_id', '=', 'tag_groups.id')
            ->whereIn('tags.id', $availableTags)
            ->groupBy('tag_groups.id')
            ->orderBy('tag_groups.name')
            ->get();
    }
}
