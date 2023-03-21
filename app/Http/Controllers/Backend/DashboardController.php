<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Traits\PersonalData;
use View;

class DashboardController extends Controller
{
    use PersonalData;
    public function __construct()
    {
        parent::__construct();
        View::share('activeNav', 'dashboard');
        $this->personalDataRightsMiddleware('dashboard');
    }

    public function index()
    {
        return view('vue-component', [
            'hasFluidContent' => false,
            'component' => 'dashboard',
        ]);
    }
}
