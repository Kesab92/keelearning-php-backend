<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Models\ContentCategories\ContentCategory;
use App\Models\Tag;
use App\Services\AppSettings;
use App\Services\TagEngine;
use Illuminate\Http\Request;
use Response;

class TagsController extends Controller
{
    const ORDER_BY = [
        'id',
        'label',
        'updated_at',
    ];
    const PER_PAGE = [
        50,
        100,
        200,
    ];

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:,tags-edit')->except('getTags');
    }

    /**
     * Returns tags data
     *
     * @param Request $request
     * @param TagEngine $tagEngine
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function index(Request $request, TagEngine $tagEngine)
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
        $search = $request->input('search');
        $filter = $request->input('filter');
        $contentCategories = $request->input('contentcategories', []);

        $this->checkFilterPermission($filter);

        $tagsQuery = $tagEngine->tagsFilterQuery(appId(), $search, $contentCategories, $filter, $orderBy, $orderDescending);

        $countTags = $tagsQuery->count();
        $tags = $tagsQuery
            ->with(['tagGroup', 'contentcategories', 'contentcategories.translationRelation'])
            ->offset($perPage * ($page - 1))
            ->limit($perPage)
            ->get();

        if($tags->isNotEmpty()) {
            $entryCounts = $tagEngine->getEntryCounts($tags->pluck('id'));
        } else {
            $entryCounts = collect([]);
        }

        $tags = array_map(function ($tag) use ($entryCounts, $tagEngine) {
            $tag['entryCount'] = $entryCounts->get($tag['id']) ?? 0;
            return $tag;
        }, $tags->toArray());

        return response()->json([
            'count' => $countTags,
            'tags' => $tags,
        ]);
    }

    /**
     * Adds the tag
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function store(Request $request) {
        $tag = new Tag();
        $tag->app_id = appId();
        $tag->label = $request->input('label');
        $tag->save();

        return response()->json([
            'tag' => $tag,
        ]);
    }

    /**
     * Returns the tag using JSON
     *
     * @param $tagId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function show($tagId) {
        $tag = $this->getTag($tagId);

        return Response::json($this->getTagResponse($tag));
    }

    /**
     * Updates the tag
     *
     * @param $tagId
     * @param Request $request
     * @param AppSettings $appSettings
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function update($tagId, Request $request, AppSettings $appSettings) {
        $tag = $this->getTag($tagId);
        $basicFields = ['label', 'exclusive', 'hideHighscore'];

        if (!$appSettings->getValue('hide_tag_groups')) {
            $basicFields[] = 'tag_group_id';
        }

        foreach($basicFields as $field) {
            if($request->has($field)) {
                $value = $request->input($field, null);
                $tag->setAttribute($field, $value);
            }
        }
        $tag->save();

        if($request->has('contentcategories')) {
            $contentCategories = $request->input('contentcategories', []);
            if(!is_array($contentCategories)) {
                $contentCategories = [$contentCategories];
            }
            $availableCategories = ContentCategory
                ::where('app_id', $tag->app_id)
                ->where('type', ContentCategory::TYPE_TAGS)
                ->pluck('id');

            $categoryIds = collect($contentCategories)->intersect($availableCategories);
            $tag->contentcategories()->syncWithPivotValues($categoryIds, [
                'type' => ContentCategory::TYPE_TAGS,
            ]);
        }

        return Response::json($this->getTagResponse($tag));
    }

    /**
     * Returns dependencies and blockers
     *
     * @param $tagId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function deleteInformation($tagId)
    {
        $tag = $this->getTag($tagId);

        return \Illuminate\Support\Facades\Response::json([
            'dependencies' => $tag->safeRemoveDependees(),
            'blockers' => $tag->getBlockingDependees(),
        ]);
    }

    /**
     * Deletes the tag
     *
     * @param $tagId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function delete($tagId) {
        $tag = $this->getTag($tagId);

        $result = $tag->safeRemove();

        if($result->isSuccessful()) {
            return Response::json([], 204);
        } else {
            return Response::json($result->getMessages(), 400);
        }
    }

    /**
     * Returns the tags of this app.
     *
     * @throws \Exception
     */
    public function getTags()
    {
        $tags = Tag::ofApp(appId())
            ->get()
            ->transform(function ($tag) {
                return [
                    'id' => $tag->id,
                    'label' => $tag->label,
                ];
            });

        return Response::json([
            'tags' => $tags,
        ]);
    }

    /**
     * Returns the tag
     *
     * @param $tagId
     * @return Tag \Illuminate\Database\Eloquent\Model\Tag
     * @throws \Exception
     */
    private function getTag($tagId)
    {
        $tag = Tag::findOrFail($tagId);

        // Check the access rights
        if ($tag->app_id != appId()) {
            app()->abort(403);
        }

        return $tag;
    }

    /**
     * Returns the tag for the response
     *
     * @param Tag $tag
     * @return Tag[]
     */
    private function getTagResponse(Tag $tag) {
        $contentCategories = $tag->contentcategories->pluck('id');
        $tag = $tag->toArray();
        $tag['contentcategories'] = $contentCategories;

        return [
            'tag' => $tag,
        ];
    }

    /**
     * Checks the filter has permissions
     *
     * @param $filter
     * @return bool
     */
    private function checkFilterPermission($filter) {
        $appSettings = app(AppSettings::class);
        $hasPermission = true;

        if ($filter === 'course') {
            $hasPermission = $appSettings->getValue('module_courses');
        }
        if ($filter === 'test') {
            $hasPermission = $appSettings->getValue('module_tests');
        }
        if ($filter === 'learningmaterial') {
            $hasPermission = $appSettings->getValue('module_learningmaterials');
        }
        if ($filter === 'quiz') {
            $hasPermission = $appSettings->getValue('module_quiz');
        }
        if ($filter === 'question') {
            $hasPermission = $appSettings->getValue('module_powerlearning');
        }
        if ($filter === 'index_card') {
            $hasPermission = $appSettings->getValue('module_index_cards');
        }
        if ($filter === 'voucher') {
            $hasPermission = $appSettings->getValue('module_vouchers');
        }
        if ($filter === 'advertisement') {
            $hasPermission = $appSettings->getValue('module_advertisements');
        }
        if ($filter === 'news') {
            $hasPermission = $appSettings->getValue('module_news');
        }
        if ($filter === 'webinar') {
            $hasPermission = $appSettings->getValue('module_webinars');
        }
        if ($filter === 'page') {
            $hasPermission = $appSettings->getValue('has_subpages');
        }

        if(!$hasPermission) {
            abort(403);
        }

        return true;
    }
}
