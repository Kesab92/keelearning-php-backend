<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Traits\PersonalData;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use View;

class QuizTeamsController extends Controller
{
    use PersonalData;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:quiz,quizteams-personaldata');
        $this->personalDataRightsMiddleware('quizteams');
        View::share('activeNav', 'quiz-teams');
    }

    /**
     * Displays the overview of quiz teams.
     *
     * @return Application|Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        return view('vue-component', [
            'hasFluidContent' => false,
            'component' => 'routed',
        ]);
    }
}
