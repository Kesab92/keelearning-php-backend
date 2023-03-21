<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class WebinarsController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:webinars,webinars-personaldata');
        \View::share('activeNav', 'webinars');
    }

    public function index()
    {
        return view('vue-component', [
            'hasFluidContent' => false,
            'component' => 'webinars',
        ]);
    }
}
