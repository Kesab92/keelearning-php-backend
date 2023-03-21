<?php

namespace App\Stats;

use App\Models\Game;
use App\Models\GameQuestionAnswer;
use App\Models\TrainingAnswer;

/**
 * Calculates the amount of answers the user answered wrong for a category.
 */
class PlayerTrainingWrongAnswersByCategory extends Statistic
{
    /**
     * @var
     */
    private $playerId;
    /**
     * @var
     */
    private $categoryId;

    public function __construct($playerId, $categoryId)
    {
        $this->playerId = $playerId;
        $this->categoryId = $categoryId;
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
                                            ->whereHas('question', function ($q) {
                                                $q->where('questions.category_id', $this->categoryId);
                                            })
                                            ->where('correct', 0)
                                            ->count();

        return $wrongAnswers;
    }

    protected function getCacheKey()
    {
        return 'player-training-wrong-answers-'.$this->playerId.'-category-'.$this->categoryId;
    }
}
