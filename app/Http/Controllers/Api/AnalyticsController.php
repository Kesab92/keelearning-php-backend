<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StatsEngine;
use Response;

class AnalyticsController extends Controller
{
    public function challengingQuestions()
    {
        return Response::json((new StatsEngine(appId()))->getChallengingQuestions(user()));
    }

    public function nemesisPlayers()
    {
        return Response::json((new StatsEngine(appId()))->getNemesisPlayers(user()));
    }

    public function quizProgress()
    {
        return Response::json((new StatsEngine(appId()))->getQuizProgress(user()));
    }

    public function strongPlayers()
    {
        return Response::json((new StatsEngine(appId()))->getStrongPlayers(user()));
    }
}
