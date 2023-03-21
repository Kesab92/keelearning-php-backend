<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Services\Users\UsersStatsExport;
use App\Traits\PersonalData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use View;

class StatsUsersController extends Controller
{
    use PersonalData;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:stats_users,users-stats');
        $this->personalDataRightsMiddleware('users');
        View::share('activeNav', 'stats.users');
    }

    public function index()
    {
        return view('vue-component', [
            'hasFluidContent' => false,
            'component' => 'routed',
        ]);
    }

    public function export(Request $request) {
        $tags = array_filter(explode(',', $request->get('tags', '')));
        return Excel::download(new UsersStatsExport($this->appSettings, $tags, Auth::user(), false, $this->showPersonalData, $this->showEmails), 'user-statistics-' . date('Y-m-d') . '.xlsx');
    }
}
