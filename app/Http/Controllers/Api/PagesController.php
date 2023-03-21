<?php

namespace App\Http\Controllers\Api;

use App\Http\APIError;
use App\Http\Controllers\Controller;
use App\Models\AppProfile;
use App\Models\Page;
use Illuminate\Support\Collection;
use Response;

class PagesController extends Controller
{
    /**
     * Returns a list of all pages.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function pages()
    {
        $responsePages = ['pages' => []];
        $pages = Page::where('app_id', user()->app_id)
            ->whereNull('parent_id')
            ->where('visible', 1)
            ->get();

        $subPages = $this->getSubPages($pages->pluck('id'));

        foreach ($pages as $page) {
            $pageResponse = [
                'id' => $page->id,
                'title' => $page->title,
                'show_in_footer' => $page->show_in_footer,
            ];
            $subPagesOfPage = $subPages->get($page->id);
            if($subPagesOfPage) {
                $subPage = $subPagesOfPage->first();
                $pageResponse['title'] = $subPage->title;
            }

            $responsePages['pages'][] = $pageResponse;
        }

        return Response::json($responsePages);
    }

    /**
     * Returns a list of all public pages which should be shown on the auth screens.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function publicAuthPages($appId, $profileId = null)
    {
        $data = ['pages' => []];
        $pages = Page::where('app_id', $appId)
            ->whereNull('parent_id')
            ->where('visible', 1)
            ->where('public', 1)
            ->where('show_on_auth', 1)
            ->get();

        $subPages = $this->getSubPages($pages->pluck('id'), $profileId);

        foreach ($pages as $page) {
            $pageResponse = [
                'id' => $page->id,
                'title' => $page->title,
            ];
            $subPagesOfPage = $subPages->get($page->id);
            if($subPagesOfPage) {
                $subPage = $subPagesOfPage->first();
                $pageResponse['title'] = $subPage->title;
            }

            $data['pages'][] = $pageResponse;
        }

        return Response::json($data);
    }

    /**
     * @param $pageId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function page($pageId)
    {
        $page = Page::where('id', $pageId)->whereNull('parent_id')->firstOrFail();

        if ($page->app_id != user()->app_id) {
            return new APIError(__('errors.no_page_access'), 403);
        }

        if (! $page->visible) {
            return new APIError(__('errors.page_invisible'), 403);
        }

        $subPage = $this->getSubPage($page->id);

        if($subPage) {
            $page = $subPage;
        }

        return Response::json([
            'title' => $page->title,
            'content' => $page->content,
        ]);
    }

    /**
     * @param $pageId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function publicPage($pageId, $profileId = null)
    {
        $page = Page::where('id', $pageId)->whereNull('parent_id')->firstOrFail();

        if (! $page->public) {
            return new APIError(__('errors.page_not_public'), 403);
        }

        if (! $page->visible) {
            return new APIError(__('errors.page_invisible'), 403);
        }

        $subPage = $this->getSubPage($page->id, $profileId);

        if ($subPage) {
            $page = $subPage;
        }

        return Response::json([
            'title'   => $page->title,
            'content' => $page->content,
        ]);
    }

    /**
     * Returns a collections of sub pages
     *
     * @param $pageIds
     * @param null $profileId
     * @return Collection
     */
    private function getSubPages($pageIds, $profileId = null) {
        $pages = $this->getSubPageQuery($profileId);
        if(!$pages) {
            return collect([]);
        }
        return $pages
            ->whereIn('parent_id', $pageIds)
            ->get()
            ->groupBy('parent_id');
    }

    /**
     * Returns one sub page
     *
     * @param $parentId
     * @param null $profileId
     * @return Page|null
     */
    private function getSubPage($parentId, $profileId = null) {
        $pages = $this->getSubPageQuery($profileId);
        if(!$pages) {
            return null;
        }
        return $pages
            ->where('parent_id', $parentId)
            ->first();
    }

    public function getToS()
    {
        $appProfile = user()->getAppProfile();
        if (! $appProfile->getValue('tos_id')) {
            return new APIError(__('errors.no_tos'));
        }
        $page = Page::find($appProfile->getValue('tos_id'));

        $subPage = $this->getSubPage($page->id, $appProfile->id);

        if($subPage) {
            $page = $subPage;
        }

        return Response::json([
            'id' => $page->id,
            'title' => $page->title,
            'content' => $page->content,
        ]);
    }

    /**
     * Generates a query which filters sub pages for the active user or given profile id
     *
     * @param null $profileId
     * @return \Illuminate\Database\Eloquent\Builder|null
     */
    private function getSubPageQuery($profileId = null) {
        $user = user();
        $pages = Page::query();
        if(!$user) {
            if(!$profileId) {
                return null;
            } else {
                $appProfile = AppProfile::find($profileId);
                if(!$appProfile) {
                    return null;
                }
                $pages = $pages->visibleToAppProfile($appProfile);
            }
        } else {
            $pages->visibleToUser($user);
        }
        return $pages;
    }
}
