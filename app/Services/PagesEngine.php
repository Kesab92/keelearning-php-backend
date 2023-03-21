<?php

namespace App\Services;

use App\Models\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class PagesEngine
{

    /**
     * Create a query for pages using filter
     * @param $appId
     * @param null $search
     * @param null $orderBy
     * @param false $descending
     * @return Page|\Illuminate\Database\Eloquent\Builder
     */
    public function pagesFilterQuery($appId, $search = null, $tags = null, $orderBy = null, $descending = false)
    {
        $pagesQuery = Page::where('app_id', $appId);

        if ($search) {
            $matchingTitles = DB::table('page_translations')
                ->join('pages', 'page_translations.page_id', '=', 'pages.id')
                ->select('pages.id')
                ->where('pages.app_id', $appId)
                ->whereRaw('page_translations.title LIKE ?', '%'.escapeLikeInput($search).'%');
            $pagesQuery->where(function ($query) use ($search, $matchingTitles) {
                $query->whereIn('id', $matchingTitles)
                    ->orWhere('id', extractHashtagNumber($search));
            });
        }
        if ($tags && count($tags)) {
            $pagesWithoutTag = in_array(-1, $tags);
            $tags = array_filter($tags, function ($tag) {
                return $tag !== '-1';
            });
            if (count($tags)) {
                $pagesQuery->where(function (Builder $query) use ($tags, $pagesWithoutTag) {
                    $query->whereHas('tags', function ($query) use ($tags) {
                        $query->whereIn('tags.id', $tags);
                    });
                    if ($pagesWithoutTag) {
                        $query->orWhereDoesntHave('tags');
                    }
                });
            } else {
                $pagesQuery->doesntHave('tags');
            }
        }

        if ($orderBy) {
            $pagesQuery->orderBy($orderBy, $descending ? 'desc' : 'asc');
        }
        return $pagesQuery;
    }
}
