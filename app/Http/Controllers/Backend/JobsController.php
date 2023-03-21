<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use View;

class JobsController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        View::share('activeNav', 'jobs');
    }

    /**
     * Shows the certificate designer.
     */
    public function listRunningJobs()
    {
        return view('vue-component', [
            'component' => 'jobs',
            'hasFluidContent' => false,
        ]);
    }
}
