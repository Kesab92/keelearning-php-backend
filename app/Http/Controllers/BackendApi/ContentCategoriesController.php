<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Models\ContentCategories\ContentCategory;
use App\Services\ContentCategories\ContentCategoriesEngine;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Response;

class ContentCategoriesController extends Controller
{
    const ORDER_BY = [
        'id',
    ];
    /**
     * @var ContentCategoriesEngine
     */
    private ContentCategoriesEngine $contentCategoriesEngine;

    public function __construct(ContentCategoriesEngine $contentCategoriesEngine)
    {
        parent::__construct();
        $this->contentCategoriesEngine = $contentCategoriesEngine;
    }

    /**
     * @param $type
     * @param Request $request
     * @param ContentCategoriesEngine $contentCategoriesEngine
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function index(Request $request)
    {
        $type = $request->input('type');
        $this->checkViewRights($type);
        $orderBy = $request->input('sortBy');
        if (! in_array($orderBy, self::ORDER_BY)) {
            $orderBy = self::ORDER_BY[0];
        }
        $orderDescending = $request->input('descending') === 'true';
        $search = $request->input('search');

        $categoriesQuery = $this->contentCategoriesEngine->categoriesFilterQuery(appId(), $type, $search, $orderBy, $orderDescending);

        $categories = $categoriesQuery
            ->with('translationRelation')
            ->withCount('contentCategoryRelations')
            ->get();

        $categories = array_map(function ($category) {
            unset($category['translation_relation']);
            return $category;
        }, $categories->values()->toArray());

        return response()->json([
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $this->checkEditRights($request->input('type'));

        $name = $request->input('name');
        $type = $request->input('type');

        if(!$name || !$type) {
            abort(400);
        }

        $category = DB::transaction(function() use ($type, $name) {
            $category = new ContentCategory();
            $category->app_id = appId();
            $category->setLanguage(defaultAppLanguage(appId()));
            $category->name = $name;
            $category->type = $type;
            $category->save();

            return $category;
        });

        return response()->json([
            'category' => $category,
        ]);
    }

    private function getCategoryResponse(ContentCategory $category) {
        $category->translations = $category->allTranslationRelations->whereIn('language', [defaultAppLanguage(), language()])->values();
        $category->unsetRelation('allTranslationRelations');
        return [
            'category' => $category,
        ];
    }

    public function show($categoryId) {
        $category = $this->contentCategoriesEngine->getCategory($categoryId, \Auth::user());
        $this->checkViewRights($category->type);
        return Response::json($this->getCategoryResponse($category));
    }

    public function update($categoryId, Request $request)
    {
        $category = $this->contentCategoriesEngine->getCategory($categoryId, \Auth::user());

        $this->checkEditRights($category->type);

        $basicFields = ['name'];
        foreach($basicFields as $field) {
            if($request->has($field)) {
                $value = $request->input($field, null);

                if($field === 'name' && !$value) {
                    abort(400);
                }

                $category->setAttribute($field, $value);
            }
        }
        $category->save();

        return Response::json($this->getCategoryResponse($category));
    }

    public function deleteInformation($categoryId)
    {
        $category = $this->contentCategoriesEngine->getCategory($categoryId, \Auth::user());
        return Response::json([
            'dependencies' => $category->safeRemoveDependees(),
            'blockers' => $category->getBlockingDependees(),
        ]);
    }

    public function delete($categoryId) {
        $category = $this->contentCategoriesEngine->getCategory($categoryId, \Auth::user());

        $this->checkEditRights($category->type);

        $result = $category->safeRemove();

        if($result->isSuccessful()) {
            return Response::json([], 204);
        } else {
            return Response::json($result->getMessages(), 400);
        }
    }

    private function checkViewRights($type) {
        switch ($type) {
            case 'courses':
                $hasRight = Auth::user()->hasRight('courses-view') || Auth::user()->hasRight('courses-edit');
                break;
            case 'keywords':
                $hasRight = Auth::user()->hasRight('keywords-edit');
                break;
            case 'tags':
                $hasRight = Auth::user()->hasRight('tags-edit');
                return;
            default:
                $hasRight = false;
                return;
        }
        if(!$hasRight) {
            app()->abort(403);
        }
    }

    private function checkEditRights($type) {
        switch ($type) {
            case 'courses':
                $hasRight = Auth::user()->hasRight('courses-edit');
                break;
            case 'keywords':
                $hasRight = Auth::user()->hasRight('keywords-edit');
                break;
            case 'tags':
                $hasRight = Auth::user()->hasRight('tags-edit');
                return;
            default:
                $hasRight = false;
                return;
        }
        if(!$hasRight) {
            app()->abort(403);
        }
    }
}
