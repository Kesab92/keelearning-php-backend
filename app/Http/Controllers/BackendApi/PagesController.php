<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\PagesEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class PagesController extends Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:,pages-edit');
    }

    /**
     * Returns pages data
     *
     * @param Request $request
     * @param PagesEngine $pagesEngine
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function index(Request $request, PagesEngine $pagesEngine)
    {
        $search = $request->input('search');
        $tags = $request->input('tags', []);

        $pagesQuery = $pagesEngine->pagesFilterQuery(appId(), $search, $tags);
        $pages = $pagesQuery
            ->with('tags', 'translationRelation')
            ->get();

        $pages = array_map(function ($page) {
            unset($page['translation_relation']);
            return $page;
        }, $pages->toArray());

        return response()->json([
            'pages' => $pages,
        ]);
    }

    /**
     * Gets all main pages
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function mainPages()
    {
        $pages = Page::where('app_id', appId())
            ->whereNull('parent_id')
            ->with('translationRelation')
            ->get();

        $pages = array_map(function ($page) {
            unset($page['translation_relation']);
            return $page;
        }, $pages->toArray());

        return response()->json([
            'pages' => $pages,
        ]);
    }

    /**
     * Gets all sub pages
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function subPages()
    {
        $pages = Page::where('app_id', appId())
            ->whereNotNull('parent_id')
            ->with('translationRelation')
            ->with('tags')
            ->get();

        $pages = array_map(function ($page) {
            unset($page['translation_relation']);
            return $page;
        }, $pages->toArray());

        return response()->json([
            'pages' => $pages,
        ]);
    }

    /**
     * Adds the page
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function store(Request $request)
    {
        $page = DB::transaction(function() use ($request) {
            $page = new Page();
            $page->app_id = appId();
            $page->setLanguage(defaultAppLanguage(appId()));
            $page->title = $request->input('title');
            $page->save();
            return $page;
        });

        return Response::json($this->getPageResponse($page));
    }

    /**
     * Returns the page JSON
     *
     * @param $pageId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function show($pageId) {
        $page = $this->getPage($pageId);
        $response = $this->getPageResponse($page);
        return Response::json($response);
    }

    /**
     * Updates the page
     *
     * @param $pageId
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function update($pageId, Request $request)
    {
        $page = $this->getPage($pageId);

        if($request->has('parent_id')) {
            $hasSubPages = Page::where('parent_id', $page->id)->exists();
            if($hasSubPages) {
                abort(403);
            }
        }

        // Update the page
        $basicFields = ['title', 'content', 'visible', 'public', 'show_on_auth', 'show_in_footer', 'parent_id'];
        foreach ($basicFields as $field) {
            if ($request->has($field)) {
                $value = $request->input($field, null);
                if($field === 'parent_id' && $value !== null) {
                    Page::where('app_id', appId())
                        ->where('id', $value)
                        ->firstOrFail();
                }
                $page->setAttribute($field, $value);
            }
        }
        if ($page->show_on_auth) {
            $page->public = true;
            $page->visible = true;
        }
        if ($page->show_in_footer) {
            $page->visible = true;
        }

        $page->save();

        if($request->has('tags') && $request->has('parent_id') && $request->input('parent_id')) {
            $page->syncTags($request->input('tags', []));
        }

        if($request->has('parent_id') && $request->input('parent_id') === null) {
            $page->tags()->detach();
        }

        $page->load('tags');

        $response = $this->getPageResponse($page);

        return Response::json($response);
    }

    /**
     * Returns dependencies and blockers
     *
     * @param $pageId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function deleteInformation($pageId)
    {
        $page = $this->getPage($pageId);
        return Response::json([
            'dependencies' => $page->safeRemoveDependees(),
            'blockers' => $page->getBlockingDependees(),
        ]);
    }

    /**
     * Deletes the page
     *
     * @param $pageId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function delete($pageId) {
        $page = $this->getPage($pageId);

        $result = $page->safeRemove();

        if($result->isSuccessful()) {
            return Response::json([], 204);
        } else {
            return Response::json($result->getMessages(), 400);
        }
    }

    /**
     * Returns the page
     *
     * @param $pageId
     * @return Page \Illuminate\Database\Eloquent\Model
     * @throws \Exception
     */
    private function getPage($pageId)
    {
        $page = Page::findOrFail($pageId);

        // Check the access rights
        if ($page->app_id != appId()) {
            app()->abort(403);
        }
        return $page;
    }

    /**
     * Returns the page for the response
     *
     * @param Page $page
     * @return Page[]
     * @throws \Exception
     */
    private function getPageResponse(Page $page) {
        $page->translations = $page->allTranslationRelations->whereIn('language', [defaultAppLanguage(), language()])->values();
        $page->unsetRelation('allTranslationRelations');

        $page->tags->transform(function($tag) {
            return $tag->id;
        });

        $response = $page->toArray();
        $response['public_link'] = $page->app->getDefaultAppProfile()->app_hosted_at . '/public/page/' . $page->id;
        $response['hasSubPages'] = Page::where('parent_id', $page->id)->exists();

        return [
            'page' => $response,
        ];
    }
}
