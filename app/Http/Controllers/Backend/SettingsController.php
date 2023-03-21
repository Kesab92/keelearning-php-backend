<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\App;
use View;

class SettingsController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:,settings-edit');
        View::share('activeNav', 'settings');
    }

    /**
     * The function displays the settings view for the app values.
     *
     * @return mixed
     * @throws \Exception
     */
    public function index()
    {
        return view('vue-component', [
            'hasFluidContent' => false,
            'component' => 'routed',
            'props' => [
                'superadmin' => isSuperAdmin(),
            ],
        ]);
    }
}
