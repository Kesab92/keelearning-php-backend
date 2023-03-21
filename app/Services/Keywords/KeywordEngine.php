<?php

namespace App\Services\Keywords;

use App\Models\Keywords\Keyword;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class KeywordEngine
{

    /**
     * Creates a query for keywords using filter
     * @param $appId
     * @param null $search
     * @param $categories
     * @param null $orderBy
     * @param false $descending
     * @return Keyword|\Illuminate\Database\Eloquent\Builder
     */
    public function keywordsFilterQuery($appId, $search = null, $categories = null, $orderBy = null, $descending = false)
    {
        $keywordsQuery = Keyword::where('app_id', $appId);

        if ($search) {
            $matchingTitles = DB::table('keyword_translations')
                ->join('keywords', 'keyword_translations.keyword_id', '=', 'keywords.id')
                ->select('keywords.id')
                ->where('keywords.app_id', $appId)
                ->whereRaw('keyword_translations.name LIKE ?', '%'.escapeLikeInput($search).'%');
            $keywordsQuery->where(function ($query) use ($search, $matchingTitles) {
                $query->whereIn('id', $matchingTitles)
                    ->orWhere('id', extractHashtagNumber($search));
            });
        }
        if ($categories && count($categories)) {
            $keywordsQuery->where(function (Builder $query) use ($categories) {
                $query->whereHas('categories', function ($query) use ($categories) {
                    $query->whereIn('content_categories.id', $categories);
                });
            });
        }
        if ($orderBy) {
            $keywordsQuery->orderBy($orderBy, $descending ? 'desc' : 'asc');
        }
        return $keywordsQuery;
    }
}
