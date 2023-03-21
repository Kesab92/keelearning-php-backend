<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Services\AppSettings;
use View;

class SuggestedQuestionsController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:suggested_questions,suggestedquestions-edit');
        View::share('activeNav', 'suggestedQuestions');
    }

    /**
     * Shows the list of questions.
     *
     * @return View
     */
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
