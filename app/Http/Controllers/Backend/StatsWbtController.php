<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use View;

class StatsWbtController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:stats_wbt,learningmaterials-stats');
        View::share('activeNav', 'stats.wbt');
    }

    /**
     * Shows the index page of WBT events.
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('vue-component', [
            'hasFluidContent' => false,
            'component' => 'stats-wbt',
        ]);
    }
}
