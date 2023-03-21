<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Services\AppSettings;
use View;

class PagesController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:,pages-edit');
        View::share('activeNav', 'pages');
    }

    public function index(AppSettings $appSettings)
    {
        return view('vue-component', [
            'hasFluidContent' => false,
            'component' => 'routed',
            'props' => [
                'has_candy_frontend' => $appSettings->getValue('has_candy_frontend'),
            ],
        ]);
    }
}
