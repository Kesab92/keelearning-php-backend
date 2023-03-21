<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\QuizTeam;
use App\Services\AppSettings;
use App\Services\StatsEngine;
use App\Stats\PlayerCorrectAnswersByCategoryBetweenDates;
use Response;

class StatsController extends Controller
{
    /**
     * Returns a list of all player names and their stats.
     *
     * @param AppSettings $settings
     * @return \Illuminate\Http\JsonResponse
     */
    public function players(AppSettings $settings)
    {
        $user = user();
        $appProfile = $user->getAppProfile();
        if ($appProfile->getValue('quiz_hide_player_statistics')) {
            $results = [];
        } else {
            $results = (new StatsEngine($user->app_id))->getAPIPlayerList();
        }

        return Response::json($results);
    }

    /**
     * Returns the current player's quiz position.
     */
    public function position()
    {
        $user = user();

        return Response::json([
            'position' => (new StatsEngine($user->app_id))->getAPIPlayerListPosition($user->id),
        ]);
    }

    /**
     * Returns the player's quiz position.
     *
     * @param $user_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userPosition($user_id)
    {
        return Response::json([
            'position' => (new StatsEngine(user()->app_id))->getAPIPlayerListPosition($user_id),
        ]);
    }

    /**
     * Returns the stats of the logged in user.
     */
    public function mine()
    {
        $user = user();
        $stats = (new StatsEngine($user->app_id))->getPlayer($user);
        $stats['position'] = (new StatsEngine($user->app_id))->getAPIPlayerListPosition($user->id);

        return Response::json($stats);
    }

    /**
     * Returns a list of all quiz teams with their stats.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function quizTeams()
    {
        $stats = new StatsEngine(user()->app_id);

        return Response::json($stats->getQuizTeamsApiList());
    }
}
