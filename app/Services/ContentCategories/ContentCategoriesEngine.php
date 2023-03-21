<?php

namespace App\Services\ContentCategories;

use App\Models\ContentCategories\ContentCategory;
use App\Models\User;
use DB;

class ContentCategoriesEngine
{
    public function getCategory($id, User $user): ContentCategory
    {
        /** @var ContentCategory $category */
        $category = ContentCategory::findOrFail($id);
        if($category->app_id !== $user->app_id && !$user->isSuperAdmin()) {
            app()->abort(404);
        }
        return $category;
    }

    public function categoriesFilterQuery($appId, $type, $search = null, $orderBy = null, $descending = false)
    {
        $categoriesQuery = ContentCategory
            ::where('app_id', $appId)
            ->where('type', $type);

        if ($search) {
            $matchingTitles = DB::table('content_category_translations')
                ->join('content_categories', 'content_category_translations.content_category_id', '=', 'content_categories.id')
                ->select('content_categories.id')
                ->where('content_categories.app_id', $appId)
                ->whereRaw('content_category_translations.name LIKE ?', escapeLikeInput('%'.$search.'%'));
            $categoriesQuery->where(function ($query) use ($search, $matchingTitles) {
                $query->whereIn('id', $matchingTitles)
                    ->orWhere('id', extractHashtagNumber($search));
            });
        }

        if ($orderBy) {
            $categoriesQuery->orderBy($orderBy, $descending ? 'desc' : 'asc');
        }

        return $categoriesQuery;
    }
}
