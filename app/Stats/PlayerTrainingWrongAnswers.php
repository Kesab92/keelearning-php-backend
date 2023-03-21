<?php

namespace App\Stats;

use App\Models\TrainingAnswer;

/**
 * Calculates the amount of answers the user answered wrong.
 */
class PlayerTrainingWrongAnswers extends Statistic
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
     * Returns the amount of answers the user answered wrong.
     *
     * @return int
     */
    protected function getValue()
    {

        // Fetch the amount of wrong answers the user gave
        $wrongAnswers = TrainingAnswer::where('user_id', $this->playerId)
                                            ->where('correct', 0)
                                            ->count();

        return $wrongAnswers;
    }

    protected function getCacheKey()
    {
        return 'player-training-wrong-answers-'.$this->playerId;
    }
}
