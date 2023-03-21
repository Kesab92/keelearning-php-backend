<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\Tag;
use App\Services\AppSettings;
use App\Services\ImageUploader;
use App\Services\LikesEngine;
use App\Services\NewsEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class NewsController extends Controller
{
    const ORDER_BY = [
        'id',
        'published_at',
    ];
    const PER_PAGE = [
        50,
        100,
        200,
    ];

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:news,news-edit');
    }

    /**
     * Returns news data
     *
     * @param Request $request
     * @param NewsEngine $newsEngine
     * @param LikesEngine $likesEngine
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, NewsEngine $newsEngine,LikesEngine $likesEngine)
    {
        $orderBy = $request->input('sortBy');
        if (! in_array($orderBy, self::ORDER_BY)) {
            $orderBy = self::ORDER_BY[0];
        }
        $orderDescending = $request->input('descending') === 'true';
        $page = (int) $request->input('page') ?? 1;
        $perPage = $request->input('rowsPerPage');
        if (! in_array($perPage, self::PER_PAGE)) {
            $perPage = self::PER_PAGE[0];
        }
        $filter = $request->input('filter');
        $search = $request->input('search');
        $tags = $request->input('tags', []);

        $newsQuery = $newsEngine->newsFilterQuery(appId(), $search, $tags, $filter, $orderBy, $orderDescending);

        $countNews = $newsQuery->count();
        $news = $newsQuery
            ->with('tags', 'translationRelation')
            ->offset($perPage * ($page - 1))
            ->limit($perPage)
            ->get();

        $likesCounts = $likesEngine->getLikesCounts($news);
        News::attachViewcountTotals($news);

        $news = array_map(function ($newsEntry) use ($likesCounts) {
            $newsEntry['cover_image_url'] = formatAssetURL($newsEntry['cover_image_url'],  '3.0.0');
            if($likesCounts->has($newsEntry['id'])) {
                $newsEntry['likes'] = $likesCounts->get($newsEntry['id']);
            } else {
                $newsEntry['likes'] = 0;
            }
            unset($newsEntry['translation_relation']);

            return $newsEntry;
        }, $news->toArray());

        return response()->json([
            'count' => $countNews,
            'news' => $news,
        ]);
    }

    /**
     * Adds the news entry
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function store(Request $request)
    {
        $news = DB::transaction(function() use ($request) {
            $newsEntry = new News();
            $newsEntry->app_id = appId();
            $newsEntry->setLanguage(defaultAppLanguage(appId()));
            $newsEntry->title = $request->input('title');
            $newsEntry->save();
            $newsEntry->syncTags($request->input('tags', []));
            return $newsEntry;
        });

        return response()->json([
            'news' => $news,
        ]);
    }

    /**
     * Returns the news entry using JSON
     *
     * @param $newsId
     * @param LikesEngine $likesEngine
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function show($newsId) {
        $newsEntry = $this->getNewsEntry($newsId);
        return Response::json($this->getNewsEntryResponse($newsEntry));
    }

    /**
     * Updates the news entry
     *
     * @param $newsId
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function update($newsId, Request $request)
    {
        $newsEntry = $this->getNewsEntry($newsId);
        $basicFields = ['title', 'content', 'published_at', 'active_until', 'cover_image_url'];
        foreach($basicFields as $field) {
            if($request->has($field)) {
                $value = $request->input($field, null);
                $newsEntry->setAttribute($field, $value);
            }
        }

        if(!$newsEntry->published_at) {
            $newsEntry->send_notification = false;
        }
        
        $newsEntry->save();

        if($request->has('tags')) {
            $newsEntry->syncTags($request->input('tags', []));
        }

        return Response::json($this->getNewsEntryResponse($newsEntry));
    }

    /**
     * Sets the cover image for the news entry
     *
     * @param Request $request
     * @param $newsId
     * @param ImageUploader $imageUploader
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function uploadCoverImage($newsId, Request $request, ImageUploader $imageUploader)
    {
        $file = $request->file('file');
        if (! $imageUploader->validate($file)) {
            app()->abort(403);
        }
        if (!$imagePath = $imageUploader->upload($file)) {
            app()->abort(400);
        }
        $imagePath = formatAssetURL($imagePath, '3.0.0');

        return \Response::json([
            'image' => $imagePath,
        ]);
    }

    /**
     * Sends notification for the news entry with the given {id}
     * @param $newsId
     * @param AppSettings $appSettings
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function notify($newsId, AppSettings $appSettings, NewsEngine $newsEngine)
    {
        $news = $this->getNewsEntry($newsId);
        if (! $news || $news->notification_sent_at) {
            return Response::json([
                'success' => false,
            ]);
        }

        // either schedule for publish date, or send immediately
        if ($news->published_at && $news->published_at->isPast()) {
            $newsEngine->sendNotification($news);
        } else {
            $news->send_notification = true;
            $news->save();
        }

        return Response::json([
            'success' => true,
        ]);
    }

    /**
     * Returns dependencies and blockers
     *
     * @param $newsId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function deleteInformation($newsId)
    {
        $news = $this->getNewsEntry($newsId);
        return Response::json([
            'dependencies' => $news->safeRemoveDependees(),
            'blockers' => $news->getBlockingDependees(),
        ]);
    }

    /**
     * Deletes the news entry
     *
     * @param $newsId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function delete($newsId) {
        $news = $this->getNewsEntry($newsId);

        $result = $news->safeRemove();

        if($result->isSuccessful()) {
            return Response::json([], 204);
        } else {
            return Response::json($result->getMessages(), 400);
        }
    }

    /**
     * Returns the news entry
     *
     * @param $newsId
     * @return News \Illuminate\Database\Eloquent\Model
     * @throws \Exception
     */
    private function getNewsEntry($newsId)
    {
        $newsEntry = News::tagRights()->findOrFail($newsId);

        if (!$newsEntry->hasPublishedAtDate()) {
            $newsEntry->published_at = null;
        }

        if (!$newsEntry->hasEndDate()) {
            $newsEntry->active_until = null;
        }

        // Check if something was found
        if (!$newsEntry) {
            app()->abort(404);
        }

        // Check the access rights
        if ($newsEntry->app_id != appId()) {
            app()->abort(403);
        }
        return $newsEntry;
    }

    /**
     * Returns the news entry for the response
     *
     * @param News $newsEntry
     * @return News[]
     * @throws \Exception
     */
    private function getNewsEntryResponse(News $newsEntry) {
        $likesEngine = new LikesEngine();
        $newsEntry->load('tags');
        $newsEntry->tags->transform(function($tag) {
            return $tag->id;
        });
        $newsEntry->translations = $newsEntry->allTranslationRelations->whereIn('language', [defaultAppLanguage(), language()])->values();
        $newsEntry->unsetRelation('allTranslationRelations');

        News::attachViewcountTotals(collect([$newsEntry]));

        $likesCounts = $likesEngine->getLikesCounts(collect([$newsEntry]));
        if($likesCounts->has($newsEntry['id'])) {
            $newsEntry['likes'] = $likesCounts->get($newsEntry['id']);
        } else {
            $newsEntry['likes'] = 0;
        }

        return [
            'newsEntry' => $newsEntry,
        ];
    }
}
