<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\Tag;
use App\Services\PermissionEngine;
use App\Services\StatsEngine;
use App\Traits\PersonalData;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use View;

class StatsTrainingController extends Controller
{
    use PersonalData;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:stats_training,questions-stats');
        $this->personalDataRightsMiddleware('users');
        View::share('activeNav', 'stats.training');
    }

    /**
     * Shows the player stats.
     *
     *
     * @param StatsEngine $stats
     * @param Request $request
     * @param PermissionEngine $permissionEngine
     * @return mixed
     * @throws \Exception
     */
    public function players(StatsEngine $stats, Request $request, PermissionEngine $permissionEngine)
    {
        // The php 7.2 garbage collector tries to garbage collect 40 times while rendering the view, so we tell him not to
        gc_disable();
        $players = $stats->getTrainingPlayersList();

        if ($tag = $request->get('tag')) {
            $players = $players->filter(function ($user) use ($tag) {
                $userTags = explode(',', $user->stats['tags']);

                return $userTags && in_array($tag, $userTags);
            });
        }
        $players = $permissionEngine->filterPlayerStatsByTag(Auth::user(), $players);

        $sort = $request->get('sort');
        $sortDesc = $request->get('sortDesc', $this->getDefaultSortDesc($sort));

        if ($sort) {
            if (Str::startsWith($sort, 'category-')) {
                $sortCategory = explode('-', $sort)[1];
                $players = $players->sortBy(function ($user) use ($sortCategory) {
                    return $user->stats['categories'][$sortCategory]['average_box'];
                }, SORT_REGULAR, $sortDesc);
            } else {
                switch ($sort) {
                    case 'all':
                        $players = $players->sortBy(function ($user) use ($sort) {
                            return $user->stats['all']['average_box'];
                        }, SORT_REGULAR, $sortDesc);
                        break;
                    case 'country':
                    case 'username':
                        if ($this->showPersonalData) {
                            $players = $players->sortBy(function ($user) use ($sort) {
                                return $user[$sort];
                            }, SORT_REGULAR, $sortDesc);
                        }
                        break;
                    default:
                        $players = $players->sortBy(function ($user) use ($sort) {
                            return $user->stats[$sort];
                        }, SORT_REGULAR, $sortDesc);
                        break;
                }
            }
        }

        $page = LengthAwarePaginator::resolveCurrentPage() ?: 1;
        $players = new LengthAwarePaginator($players->forPage($page, 300), $players->count(), 300, $page, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
        ]);

        $viewTemplate = 'stats.training.main';

        if (App::find(appId())->usePowerLearning()) {
            $viewTemplate = 'stats.training.main-powerlearning';
        }

        $view = view($viewTemplate, [
            'type'        => 'players',
            'tags'        => Tag::ofApp(appId())->orderBy('label')->rights(Auth::user())->get(),
            'players'     => $players->appends($request->except('page')),
            'settings'    => $this->appSettings,
            'selectedTag' => $tag,
            'showEmails'  => $this->showEmails,
            'showPersonalData' => $this->showPersonalData,
            'sortBy'      => $sort,
            'sortDesc'    => $sortDesc,
            'isFullAdmin' => Auth::user()->isFullAdmin(),
        ]);
        gc_enable();

        return $view;
    }

    private function getDefaultSortDesc($sort)
    {
        $ascSorters = ['country', 'username'];
        if (in_array($sort, $ascSorters)) {
            return 0;
        }

        return 1;
    }
}
