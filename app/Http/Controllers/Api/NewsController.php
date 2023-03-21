<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Like;
use App\Models\News;
use App\Services\CommentEngine;
use App\Services\LikesEngine;
use Response;

class NewsController extends Controller
{
    /**
     * Returns a list of all news.
     *
     * @param LikesEngine $likesEngine
     * @param CommentEngine $commentEngine
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function news(LikesEngine $likesEngine, CommentEngine $commentEngine)
    {
        $user = user();

        $allNews = News::visibleToUser($user)
                       ->orderBy('created_at', 'desc')
                       ->get();

        $news = [
            'news' => [],
            'app'  => $user->app_id,
        ];

        $likesCount = $likesEngine->getLikesCounts($allNews);
        $userLikes = $likesEngine->getUserLikes($allNews, $user);

        $commentsCount = $commentEngine->getCommentsCount($allNews, $user);

        foreach ($allNews as $newsEntry) {
            $news['news'][] = [
                'content'         => $newsEntry->content,
                'cover_image'     => formatAssetURL($newsEntry->cover_image), // TODO: legacy
                'cover_image_url' => formatAssetURL($newsEntry->cover_image_url),
                'id'              => $newsEntry->id,
                'published_at'    => $newsEntry->published_at->toDateTimeString(),
                'timestamp'       => $newsEntry->updated_at->format('d.m.Y'), // @deprecated
                'title'           => $newsEntry->title,
                'likes_count'     => $likesCount->get($newsEntry->id, 0),
                'likes_it'        => $userLikes->contains($newsEntry->id),
                'comment_count'     => $commentsCount->get($newsEntry->id, 0),
            ];
        }

        return Response::json($news);
    }

    /**
     * Returns a single news entry by ID.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEntryById($id, LikesEngine $likesEngine)
    {
        $entry = News::visibleToUser(user())
                       ->where('id', $id)
                       ->firstOrFail();

        $likeCount = $likesEngine->likesCount(Like::TYPE_NEWS, $entry->id);

        return Response::json([
            'content'         => $entry->content,
            'cover_image'     => formatAssetURL($entry->cover_image), // TODO: legacy
            'cover_image_url' => formatAssetURL($entry->cover_image_url),
            'id'              => $entry->id,
            'published_at'    => $entry->published_at->toDateTimeString(),
            'timestamp'       => $entry->updated_at->format('d.m.Y'), // @deprecated
            'title'           => $entry->title,
            'likes_count'     => $likeCount,
        ]);
    }
}
