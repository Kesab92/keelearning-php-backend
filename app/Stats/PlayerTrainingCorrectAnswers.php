<?php

namespace App\Stats;

use App\Models\Game;
use App\Models\GameQuestionAnswer;
use App\Models\TrainingAnswer;

/**
 * Calculates the amount of answers the user answered correctly.
 */
class PlayerTrainingCorrectAnswers extends Statistic
{
    /**
     * @var
     */
    private $playerId;

    public function __construct($playerId)
    {
        $this->playerId = $playerId;
    }

    protected function getCacheDuration()
    {
        return 0;
    }

    /**
     * Returns the amount of answers the user answered correctly.
     *
     * @return int
     */
    protected function getValue()
    {

        // Fetch the amount of correct answers the user gave
        $correctAnswers = TrainingAnswer::where('user_id', $this->playerId)
                                            ->where('correct', 1)
                                            ->count();

        return $correctAnswers;
    }

    protected function getCacheKey()
    {
        return 'player-training-correct-answers-'.$this->playerId;
    }
}
