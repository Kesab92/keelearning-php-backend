<?php

namespace App\Services;

use App\Jobs\NewsPublished;
use App\Models\News;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class NewsEngine
{
    /**
     * Removes the cover image from the given news entry.
     *
     * @param $news
     */
    public function removeCoverImage($news)
    {
        if ($news->cover_image) {
            // Check if the file is used more than once
            if (News::where('cover_image', $news->cover_image)->count() < 2) {
                unlink(public_path($news->cover_image));
            }
            $news->cover_image = null;
            $news->save();
        }
    }

    /**
     * Sends notification for a given news entry.
     *
     * @param $news
     */
    public function sendNotification($news)
    {
        NewsPublished::dispatch($news);
        $news->notification_sent_at = Carbon::now();
        $news->save();
    }

    /**
     * Create a query for news using filter
     * @param $appId
     * @param null $search
     * @param null $tags
     * @param null $filter
     * @param null $orderBy
     * @param false $descending
     * @return News|\Illuminate\Database\Eloquent\Builder
     */
    public function newsFilterQuery($appId, $search = null, $tags = null, $filter = null, $orderBy = null, $descending = false)
    {
        $newsQuery = News::tagRights()
            ->where('app_id', $appId);

        if ($search) {
            $matchingTitles = DB::table('news_translations')
                ->join('news', 'news_translations.news_id', '=', 'news.id')
                ->select('news.id')
                ->where('news.app_id', $appId)
                ->whereRaw('news_translations.title LIKE ?', '%'.escapeLikeInput($search).'%');
            $newsQuery->where(function ($query) use ($search, $matchingTitles) {
                $query->whereIn('id', $matchingTitles)
                    ->orWhere('id', extractHashtagNumber($search));
            });
        }
        if ($filter === 'active') {
            $newsQuery->where(function ($query) {
                $query->whereNull('published_at');
                $query->orWhere(function ($query) {
                    $query->whereNotNull('published_at')->where(function ($query) {
                        $query->where('active_until', '>=', Carbon::now()->startOfDay())->orWhereNull('active_until');
                    });
                });
            });
        }
        if ($filter === 'visible') {
            $newsQuery->where(function ($query) {
                $query->whereNotNull('published_at');
                $query->where(function ($query) {
                    $query->where('active_until', '>=', Carbon::now()->startOfDay())->orWhereNull('active_until');
                });
            });
        }
        if ($filter === 'expired') {
            $newsQuery->where('published_at', '<', Carbon::now()->startOfDay());
            $newsQuery->where('active_until', '<', Carbon::now()->startOfDay());
        }
        if ($tags && count($tags)) {
            $newsWithoutTag = in_array(-1, $tags);
            $tags = array_filter($tags, function ($tag) {
                return $tag !== '-1';
            });
            if (count($tags)) {
                $newsQuery->where(function (Builder $query) use ($tags, $newsWithoutTag) {
                    $query->whereHas('tags', function ($query) use ($tags) {
                        $query->whereIn('tags.id', $tags);
                    });
                    if ($newsWithoutTag) {
                        $query->orWhereDoesntHave('tags');
                    }
                });
            } else {
                $newsQuery->doesntHave('tags');
            }
        }
        if ($orderBy) {
            $newsQuery->orderBy($orderBy, $descending ? 'desc' : 'asc');
        }
        return $newsQuery;
    }
}
