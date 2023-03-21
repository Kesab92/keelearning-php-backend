<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use View;

class FormsController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:forms,forms-edit');
        View::share('activeNav', 'forms');
    }

    public function index()
    {
        return view('vue-component', [
            'hasFluidContent' => false,
            'component' => 'routed',
        ]);
    }
}
