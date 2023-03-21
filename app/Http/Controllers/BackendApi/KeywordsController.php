<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Models\ContentCategories\ContentCategory;
use App\Models\Keywords\Keyword;
use App\Services\Keywords\KeywordEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class KeywordsController extends Controller
{
    const ORDER_BY = [
        'id',
        'created_at',
    ];
    const PER_PAGE = [
        50,
        100,
        200,
    ];

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:keywords,keywords-edit');
    }

    /**
     * Returns keywords data
     *
     * @param Request $request
     * @param KeywordEngine $keywordEngine
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, KeywordEngine $keywordEngine)
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
        $categories = $request->input('categories', []);

        $keywordsQuery = $keywordEngine->keywordsFilterQuery(appId(), $search, $categories, $orderBy, $orderDescending);

        $countKeywords = $keywordsQuery->count();
        $keywords = $keywordsQuery
            ->with('translationRelation', 'categories')
            ->offset($perPage * ($page - 1))
            ->limit($perPage)
            ->get();

        $keywords = array_map(function ($keyword){
            unset($keyword['translation_relation']);
            $keyword['categories'] = collect($keyword['categories'])->pluck('id');
            return $keyword;
        }, $keywords->toArray());

        return response()->json([
            'count' => $countKeywords,
            'keywords' => $keywords,
        ]);
    }

    /**
     * Adds the keyword
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function store(Request $request)
    {

        $keyword = DB::transaction(function() use ($request) {
            $keyword = new Keyword();
            $keyword->app_id = appId();
            $keyword->setLanguage(defaultAppLanguage(appId()));
            $keyword->name = $request->input('name');
            $keyword->save();
            return $keyword;
        });

        return response()->json([
            'keyword' => $keyword,
        ]);
    }

    /**
     * Returns the keyword using JSON
     *
     * @param $keywordId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function show($keywordId) {
        $keyword = $this->getKeyword($keywordId);
        $keyword->load([
            'categories',
        ]);
        return Response::json($this->getKeywordResponse($keyword));
    }

    /**
     * Updates the keyword
     *
     * @param $keywordId
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function update($keywordId, Request $request)
    {
        $keyword = $this->getKeyword($keywordId);
        $basicFields = ['name', 'description'];
        foreach($basicFields as $field) {
            if($request->has($field)) {
                $value = $request->input($field, null);
                $keyword->setAttribute($field, $value);
            }
        }
        $keyword->save();

        if($request->has('categories')) {
            $categories = $request->input('categories', []);
            if(!is_array($categories)) {
                $categories = [$categories];
            }
            $availableCategories = ContentCategory
                ::where('app_id', $keyword->app_id)
                ->where('type', ContentCategory::TYPE_KEYWORDS)
                ->pluck('id');

            $categoryIds = collect($categories)->intersect($availableCategories);
            $keyword->categories()->syncWithPivotValues($categoryIds, [
                'type' => ContentCategory::TYPE_KEYWORDS,
            ]);
        }

        return Response::json($this->getKeywordResponse($keyword));
    }

    /**
     * Returns dependencies and blockers
     *
     * @param $keywordId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function deleteInformation($keywordId)
    {
        $keyword = $this->getKeyword($keywordId);
        return Response::json([
            'dependencies' => $keyword->safeRemoveDependees(),
            'blockers' => $keyword->getBlockingDependees(),
        ]);
    }

    /**
     * Deletes the keyword
     *
     * @param $keywordId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function delete($keywordId) {
        $keyword = $this->getKeyword($keywordId);

        $result = $keyword->safeRemove();

        if($result->isSuccessful()) {
            return Response::json([], 204);
        } else {
            return Response::json($result->getMessages(), 400);
        }
    }

    /**
     * Returns the keyword
     *
     * @param $keywordId
     * @return Keyword \Illuminate\Database\Eloquent\Model\Keywords
     * @throws \Exception
     */
    private function getKeyword($keywordId)
    {
        $keyword = Keyword::findOrFail($keywordId);

        // Check the access rights
        if ($keyword->app_id != appId()) {
            app()->abort(403);
        }
        return $keyword;
    }

    /**
     * Returns the keyword for the response
     *
     * @param Keyword $keyword
     * @return Keyword[]
     * @throws \Exception
     */
    private function getKeywordResponse(Keyword $keyword) {
        $keyword->translations = $keyword->allTranslationRelations->whereIn('language', [defaultAppLanguage(), language()])->values();
        $keyword->unsetRelation('allTranslationRelations');

        $categories = $keyword->categories->pluck('id');
        $keyword = $keyword->toArray();
        $keyword['categories'] = $categories;

        return [
            'keyword' => $keyword,
        ];
    }
}
