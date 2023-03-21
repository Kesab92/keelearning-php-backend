<?php

namespace App\Stats;

use App\Models\Game;
use App\Models\GameQuestionAnswer;
use App\Models\TrainingAnswer;

/**
 * Calculates the amount of answers the user answered correctly for a category.
 */
class PlayerTrainingCorrectAnswersByCategory extends Statistic
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
     * Returns the amount of answers the user answered correctly.
     *
     * @return int
     */
    protected function getValue()
    {

        // Fetch the amount of correct answers the user gave
        $correctAnswers = TrainingAnswer::where('user_id', $this->playerId)
                                            ->whereHas('question', function ($q) {
                                                $q->where('questions.category_id', $this->categoryId);
                                            })
                                            ->where('correct', 1)
                                            ->count();

        return $correctAnswers;
    }

    protected function getCacheKey()
    {
        return 'player-training-correct-answers-'.$this->playerId.'-category-'.$this->categoryId;
    }
}
