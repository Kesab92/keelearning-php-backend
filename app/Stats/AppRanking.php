<?php

namespace App\Stats;

use App\Models\App;
use App\Services\Sorting;

/**
 * Calculates the ranking inside the app.
 */
class AppRanking extends Statistic
{
    private $appId;

    public function __construct($appId)
    {
        $this->appId = $appId;
    }

    /**
     * Returns the ranking of that app.
     *
     * @return int
     */
    protected function getValue()
    {
        $app = App::find($this->appId);

        // Get the rankings of every single user of this app
        $ranking = $app->users->map(function ($user) {
            $wonGames = (new PlayerGameWins($user->id))->fetch();
            $correctAnswers = (new PlayerCorrectAnswers($user->id))->fetch();

            $ranking = [
                'id' => $user->id,
                'win_count' => $wonGames,
                'correct_answers_count' => $correctAnswers,
            ];

            return $ranking;
        })->toArray();

        // Sort them by their wincount or by correct answers if they won the same number of games
        $ranking = Sorting::sortRankByGameWinsAndCorrectAnswers($ranking);

        return $ranking;
    }

    protected function getCacheKey()
    {
        return 'app-ranking-'.$this->appId;
    }

    protected function getCacheTags()
    {
        return ['app-'.$this->appId];
    }
}
