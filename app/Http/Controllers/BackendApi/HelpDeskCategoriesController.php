<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Models\HelpdeskCategory;
use App\Models\HelpdeskPage;
use Illuminate\Http\Request;
use Response;

class HelpDeskCategoriesController extends Controller
{
    /**
     *  Returns the counts of the articles and categories.
     */
    public function findCounts()
    {
        $faqArticles = HelpdeskPage::where('type', HelpdeskPage::CATEGORY_FAQ)->count();
        $knowledgeArticles = HelpdeskPage::where('type', HelpdeskPage::CATEGORY_KNOWLEDGE_BASE)->count();
        $knowledgeCategories = HelpdeskCategory::count();

        return Response::json([
            'success' => true,
            'data' => [
                'faq' => $faqArticles,
                'knowledgeArticles' => $knowledgeArticles,
                'knowledgeCategories' => $knowledgeCategories,
            ],
        ]);
    }

    /**
     * Retrieves all helpdesk categories & page titles for the knowledge base.
     * @return \Illuminate\Http\JsonResponse
     */
    public function findCategoriesWithPages()
    {
        $categories = HelpdeskCategory::all();
        $pages = HelpdeskPage::where('type', HelpdeskPage::CATEGORY_KNOWLEDGE_BASE)
                    ->whereIn('category', $categories->map(function ($item) {
                        return $item->id;
                    }))
                    ->select(['id', 'title', 'category'])
                    ->get();

        foreach ($categories as $category) {
            $category->pages = $pages->filter(function ($item) use ($category) {
                return $item->category === $category->id;
            });
        }

        return Response::json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
        ]);

        $category = new HelpdeskCategory();
        $category->name = $request->input('name');
        $category->save();

        return Response::json([
            'success' => true,
        ]);
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
        ]);

        $category = HelpdeskCategory::find($id);
        if (! $category) {
            return Response::json([
                'success' => false,
            ]);
        }

        $category->name = $request->input('name');
        $category->save();

        return Response::json([
            'success' => true,
        ]);
    }

    /**
     * Removes a category.
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function remove($id)
    {
        $category = HelpdeskCategory::find($id);
        if (! $category) {
            return Response::json([
                'success' => false,
            ]);
        }

        if (HelpdeskPage::where('category', $category->id)->count() > 0) {
            return Response::json([
                'success' => false,
                'error' => 'MOVE_PAGES',
            ]);
        }

        $category->delete();

        return Response::json([
            'success' => true,
        ]);
    }

    /**
     * Updates the sort order.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSortOrder(Request $request)
    {
        $this->validate($request, [
            'categories' => 'required',
        ]);

        $categories = $request->input('categories');
        if (! $categories || ! is_array($categories)) {
            return Response::json([
                'success' => false,
            ]);
        }

        foreach ($categories as $category) {
            $helpDeskCategory = HelpdeskCategory::find($category['id']);
            $helpDeskCategory->sortIndex = $category['sortIndex'];
            $helpDeskCategory->save();
        }

        return Response::json([
            'success' => true,
        ]);
    }
}
