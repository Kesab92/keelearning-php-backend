<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use View;

class RatingsController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:,settings-ratings');
        View::share('activeNav', 'stats.ratings');
    }

    /**
     * Shows the ratings page with given ratings.
     */
    public function index()
    {
        return view('vue-component', [
            'hasFluidContent' => false,
            'component' => 'ratings',
        ]);
    }
}
