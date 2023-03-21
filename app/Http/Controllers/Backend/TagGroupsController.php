<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use View;

class TagGroupsController extends Controller
{
    /**
     * TagGroupsController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:tag_groups,tags-edit');
        View::share('activeNav', 'tag_groups');
    }

    /**
     * Shows the overview page of tag groups.
     */
    public function index()
    {
        return view('vue-component', [
            'hasFluidContent' => false,
            'component' => 'tag-group-management',
        ]);
    }
}
