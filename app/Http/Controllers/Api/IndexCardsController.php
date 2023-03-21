<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AppSettings;
use App\Services\IndexCardEngine;
use Illuminate\Support\Facades\Request;
use Response;

class IndexCardsController extends Controller
{
    /**
     * @var IndexCardEngine
     */
    private $indexCardEngine;

    public function __construct(IndexCardEngine $indexCardEngine)
    {
        parent::__construct();
        $this->indexCardEngine = $indexCardEngine;
    }

    /**
     * Gets all categories.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function categories(AppSettings $appSettings)
    {
        $categories = user()->getIndexCardCategories();
        if ($appSettings->getValue('sort_categories_alphabetically')) {
            $categories = $categories->sort(function ($categoryA, $categoryB) {
                if ($categoryA->categorygroup_name != $categoryB->categorygroup_name) {
                    return strtolower($categoryA->categorygroup_name) > strtolower($categoryB->categorygroup_name);
                }

                return strtolower($categoryA->name) > strtolower($categoryB->name);
            })->values();
        }
        $categories->transform(function ($category) {
            $category['cover_image'] = formatAssetURL($category['cover_image']);
            $category['cover_image_url'] = formatAssetURL($category['cover_image_url']);
            $category['category_icon'] = formatAssetURL($category['category_icon']);
            $category['category_icon_url'] = formatAssetURL($category['category_icon_url']);

            return $category;
        });

        return Response::json($categories->toArray());
    }

    /**
     * Fetches all cards.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function cards()
    {
        $indexcards = $this->indexCardEngine->getAllCards(user()->app_id);
        $indexcards->transform(function ($indexcard) {
            $indexcard->cover_image = formatAssetURL($indexcard->cover_image);
            $indexcard->cover_image_url = formatAssetURL($indexcard->cover_image_url);

            return $indexcard;
        });

        return Response::json($indexcards);
    }

    /**
     * Updates the user's cards.
     */
    public function update()
    {
        $this->indexCardEngine->updateLearnBoxCards(collect(Request::get('entries')), user());

        return Response::json([]);
    }

    /**
     * Returns the user's current learnstate.
     */
    public function savedata()
    {
        return Response::json($this->indexCardEngine->getSavedata(user()->id)->values());
    }
}
