<?php

namespace App\Services;

use App\Models\IndexCard;
use App\Models\Question;
use App\Models\Tag;
use App\Models\User;
use App\Services\TAGs\TAGChange;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TagEngine
{
    const RELATIONSHIPS_FOR_COUNT_QUERY = [
        'advertisement_tag',
        'appointment_tags',
        'category_tag',
        'competition_tag',
        'course_tag',
        'form_tags',
        'learning_material_folder_tags',
        'learning_material_tags',
        'news_tag',
        'page_tag',
        'test_tags',
        'voucher_tags',
        'webinar_tags',
    ];

    /**
     * Create a query for tags using filter
     *
     * @param $appId
     * @param null $search
     * @param null $categories
     * @param null $filter
     * @param null $orderBy
     * @param false $descending
     * @return Tag|\Illuminate\Database\Eloquent\Builder
     */
    public function tagsFilterQuery($appId, $search = null, $categories = null, $filter = null, $orderBy = null, $descending = false) {

        $tagsQuery = Tag::where('app_id', $appId);

        if ($search) {
            $tagsQuery->where(function ($query) use ($search) {
                $query->whereRaw('label LIKE ?', '%'.escapeLikeInput($search).'%')
                    ->orWhere('id', extractHashtagNumber($search));
            });
        }
        if($filter === 'news') {
            $tagsQuery->whereHas('news');
        }
        if($filter === 'course') {
            $tagsQuery->whereHas('courses');
        }
        if($filter === 'test') {
            $tagsQuery->whereHas('tests');
        }
        if($filter === 'learningmaterial') {
            $tagsQuery->where(function (Builder $query) {
                $query->whereHas('learningmaterials');
                $query->orWhereHas('learningmaterialfolders');
            });
        }
        if($filter === 'voucher') {
            $tagsQuery->whereHas('vouchers');
        }
        if($filter === 'advertisement') {
            $tagsQuery->whereHas('advertisements');
        }
        if($filter === 'webinar') {
            $tagsQuery->whereHas('webinars');
        }
        if($filter === 'page') {
            $tagsQuery->whereHas('pages');
        }
        if($filter === 'index_card') {
            $categories = IndexCard::select('category_id')
                ->distinct('category_id')
                ->where('app_id', $appId)
                ->whereNotNull('category_id')
                ->get()->pluck('category_id');

            $tagsQuery->whereHas('categories', function (Builder $query) use ($categories) {
                $query->whereIn('category_id', $categories);
            });
        }
        if($filter === 'quiz' || $filter === 'question') {
            $categories = Question::select('category_id')
                ->distinct('category_id')
                ->where('app_id', $appId)
                ->whereNotNull('category_id')
                ->get()->pluck('category_id');

            $tagsQuery->whereHas('categories', function (Builder $query) use ($categories) {
                $query->whereIn('category_id', $categories);
            });
        }
        if ($filter === 'none') {
            $tagsQuery->whereDoesntHave('webinars')
                ->whereDoesntHave('courses')
                ->whereDoesntHave('tests')
                ->whereDoesntHave('learningmaterials')
                ->whereDoesntHave('learningmaterialfolders')
                ->whereDoesntHave('vouchers')
                ->whereDoesntHave('categories')
                ->whereDoesntHave('pages')
                ->whereDoesntHave('news');
        }

        if ($categories && count($categories)) {
            $tagsQuery->where(function (Builder $query) use ($categories) {
                $query->whereHas('contentcategories', function ($query) use ($categories) {
                    $query->whereIn('content_categories.id', $categories);
                });
            });
        }

        if ($orderBy) {
            $tagsQuery->orderBy($orderBy, $descending ? 'desc' : 'asc');
        }

        return $tagsQuery;
    }

    /**
     * Returns the tag ids which can be added to the user without group duplicates.
     *
     * @param array $tagIds
     * @param User $user
     *
     * @return TAGChange
     */
    public function getValidAdditionalTags(array $tagIds, User $user) : TAGChange
    {
        $existingUserTagGroupIds = $user
            ->tags()
            ->whereNotNull('tag_group_id')
            ->pluck('tags.id', 'tags.tag_group_id')
            ->toArray();

        /** @var Tag[] $usedTags */
        $usedTags = Tag::whereIn('id', $tagIds)->get();
        $newUserTagGroupIds = [];
        $add = [];
        $remove = [];
        // Check if the user already has a tag in one of the tag groups and collect information on
        // which tags to add and which ones to remove
        foreach ($usedTags as $tag) {
            if ($tag->tag_group_id) {
                if (in_array($tag->tag_group_id, $newUserTagGroupIds)) {
                    // We already added a tag for this tag group
                    continue;
                }
                if (array_key_exists($tag->tag_group_id, $existingUserTagGroupIds)) {
                    // The user already has a tag in this tag group. Remove the old tag.
                    $remove[] = $existingUserTagGroupIds[$tag->tag_group_id];
                }
                $newUserTagGroupIds[] = $tag->tag_group_id;
            }
            $add[] = $tag->id;
        }

        // Remove tags the user already has
        $userTagIds = $user->tags()->pluck('tags.id')->toArray();
        $add = array_diff($add, $userTagIds);

        $tagChange = new TAGChange();
        $tagChange->setAdd($add);
        $tagChange->setRemove($remove);

        return $tagChange;
    }

    /**
     * Returns sum entries which include the tag
     *
     * @param $tagIds
     * @return Collection
     */
    public function getEntryCounts(Collection $tagIds) {
        $query = 'SELECT tag_id, SUM(count) as total FROM ( ';
        foreach (self::RELATIONSHIPS_FOR_COUNT_QUERY as $relationship) {
            $query .= ' SELECT tag_id, COUNT(*) as count FROM ' . $relationship . ' WHERE tag_id IN ('. $tagIds->implode(',') . ') GROUP BY tag_id UNION ALL';
        }
        $query = substr($query, 0, -9);
        $query .= ') as counts GROUP BY tag_id';

        return collect(DB::select($query))->pluck('total','tag_id');
    }
}
