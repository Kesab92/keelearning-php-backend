<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Services\Users\UserStatsEngine;
use App\Traits\PersonalData;
use Auth;
use Illuminate\Http\Request;

class StatsUsersController extends Controller
{
    use PersonalData;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:stats_users,users-stats');
        $this->personalDataRightsMiddleware('users');
    }

    /**
     * Shows the player stats.
     *
     * @param UserStatsEngine $userStatsEngine
     * @param Request $request
     * @return mixed
     * @throws \Exception
     */
    public function index(UserStatsEngine $userStatsEngine, Request $request)
    {
        $tags = $request->get('tags', []);
        $page = $request->input('page', 1);
        $perPage = min($request->input('rowsPerPage', 50), 200);
        $sortBy = $request->input('sortBy', 'id');
        $sortDescending = $request->input('descending', 'false') === 'true';

        $data = $userStatsEngine->getUserStats($tags, $this->appSettings, $page, $perPage, $sortBy, $sortDescending, Auth::user(), false, $this->showPersonalData, $this->showEmails);

        return \Response::json([
            'users' => $data['users'],
            'count' => $data['userCount'],
            'headers' => $data['headers'],
            'metaFields' => App::find(appId())->getUserMetaDataFields($this->showPersonalData),
        ]);
    }
}
