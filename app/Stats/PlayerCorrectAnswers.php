<?php

namespace App\Stats;

use App\Models\Game;
use App\Models\GameQuestionAnswer;
use DB;

/**
 * Calculates the amount of answers the user answered correctly.
 */
class PlayerCorrectAnswers extends Statistic
{
    private $playerId;
    private static $_preloadData;

    public function __construct($playerId)
    {
        $this->playerId = $playerId;
    }

    public static function preload($appId)
    {
        self::$_preloadData = GameQuestionAnswer::join('users', 'users.id', '=', 'game_question_answers.user_id')
            ->where('users.app_id', $appId)
            ->where('result', 1)
            ->groupBy('user_id')
            ->select(DB::RAW('COUNT(*) as count, game_question_answers.user_id as user_id'))
            ->pluck('count', 'user_id');
    }

    /**
     * Returns the amount of answers the user answered correctly.
     *
     * @return int
     */
    protected function getValue()
    {
        if (self::$_preloadData) {
            return isset(self::$_preloadData[$this->playerId]) ? self::$_preloadData[$this->playerId] : 0;
        }

        // Fetch the amount of correct answers the user gave
        $correctAnswers = GameQuestionAnswer::ofUser($this->playerId)
                                            ->where('result', 1)
                                            ->count();

        return $correctAnswers;
    }

    protected function getCacheKey()
    {
        return 'player-correct-answers-'.$this->playerId;
    }

    protected function getCacheTags()
    {
        return ['player-'.$this->playerId];
    }
}
