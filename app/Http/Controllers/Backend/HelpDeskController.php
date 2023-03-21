<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;

class HelpDeskController extends Controller
{
    /**
     * Shows the faq page.
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('vue-component', [
            'hasFluidContent' => true,
            'component' => 'help',
        ]);
    }

    public function knowledge()
    {
        return view('vue-component', [
            'component' => 'knowledge-base',
            'hasFluidContent' => false,
        ]);
    }

    public function faq()
    {
        return view('vue-component', [
            'component' => 'faq',
            'hasFluidContent' => false,
        ]);
    }
}
