<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Services\AppSettings;
use View;

class StatsQuizController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:stats_quiz_challenge,questions-stats');
        View::share('activeNav', 'stats.quiz');
    }

    /**
     * Shows the quiz stats.
     *
     * @param AppSettings $settings
     * @return view
     */
    public function index(AppSettings $settings)
    {
        return view('vue-component', [
            'hasFluidContent' => false,
            'component' => 'routed',
            'props' => [
                'has_candy_frontend' => $settings->getValue('has_candy_frontend'),
            ],
        ]);
    }
}
