<?php namespace App\Services;

use App\Models\Category;
use App\Models\Categorygroup;
use App\Models\User;
use DB;
use Illuminate\Database\Eloquent\Builder;

class CategoryEngine {

    /**
     * Creates a query builder to return all user ids which have access to a given list of category ids
     *
     * @param $categoryIds
     * @param $appId
     * @return \Illuminate\Database\Query\Builder
     */
    public function usersWithAccessToCategories($categoryIds, $appId)
    {
        return User
            ::select('users.id')
            ->join('categories', 'categories.app_id', 'users.app_id')
            ->where('users.app_id', $appId)
            ->whereIn('categories.id', $categoryIds)
            ->where(function(Builder $q) use ($appId) {
                $q
                    ->whereIn('categories.id', $this->openCategories($appId))
                    ->orWhereIn('users.id', $this->categoryUsers(\DB::raw('categories.id')));
            })
            ->where(function(Builder $q) use ($appId) {
                $q
                    ->whereNull('categories.categorygroup_id')
                    ->orWhereIn('categories.categorygroup_id', $this->openCategoryGroups($appId))
                    ->orWhereIn('users.id', $this->categorygroupUsers(\DB::raw('categories.categorygroup_id')));
            })
            ->groupBy('users.id');
    }

    /**
     * Creates a query builder for all open categories (e.g. categories without a TAG) of a given app
     *
     * @param $appId
     * @return \Illuminate\Database\Query\Builder
     */
    public function openCategories($appId)
    {
        return Category
            ::select('categories.id')
            ->leftJoin('category_tag', 'category_tag.category_id', 'categories.id')
            ->where('categories.app_id', $appId)
            ->whereNull('category_tag.id')
            ->groupBy('categories.id');
    }

    /**
     * Creates a query builder for all users which have some overlapping tags with a category
     *
     * @param $categoryId
     * @return \Illuminate\Database\Query\Builder
     */
    public function categoryUsers($categoryId)
    {
        return DB
            ::table('category_tag')
            ->select('tag_user.user_id')
            ->join('tag_user', 'tag_user.tag_id', 'category_tag.tag_id')
            ->where('category_tag.category_id', $categoryId)
            ->groupBy('tag_user.user_id');
    }

    /**
     * Creates a query builder for all open category groups (e.g. category groups without a TAG) of a given app
     *
     * @param $appId
     * @return \Illuminate\Database\Query\Builder
     */
    public function openCategoryGroups($appId)
    {
        return Categorygroup
            ::select('categorygroups.id')
            ->leftJoin('categorygroup_tag', 'categorygroup_tag.categorygroup_id', 'categorygroups.id')
            ->where('categorygroups.app_id', $appId)
            ->whereNull('categorygroup_tag.id')
            ->groupBy('categorygroups.id');
    }

    /**
     * Creates a query builder for all users which have some overlapping tags with a category group
     *
     * @param $categorygroupId
     * @return \Illuminate\Database\Query\Builder
     */
    public function categorygroupUsers($categorygroupId)
    {
        return DB
            ::table('categorygroup_tag')
            ->select('tag_user.user_id')
            ->join('tag_user', 'tag_user.tag_id', 'categorygroup_tag.tag_id')
            ->where('categorygroup_tag.categorygroup_id', $categorygroupId)
            ->groupBy('tag_user.user_id');

    }
}
