<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use View;

class AppointmentsController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:appointments,appointments-edit|appointments-view');
        View::share('activeNav', 'appointments');
    }

    public function index()
    {
        return view('vue-component', [
            'hasFluidContent' => false,
            'component' => 'routed',
        ]);
    }
}
