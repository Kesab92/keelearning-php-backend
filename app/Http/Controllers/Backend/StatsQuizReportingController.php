<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Services\AppSettings;

class StatsQuizReportingController extends Controller
{
    /**
     * Displays all reportings.
     *
     * @return mixed
     * @throws \Exception
     */
    public function overview(AppSettings $appSettings)
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
