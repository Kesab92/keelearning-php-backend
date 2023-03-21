<?php

namespace App\Stats;

use App\Models\Game;
use App\Models\GameQuestionAnswer;
use DB;

/**
 * Calculates the amount of answers the user answered correctly for a category.
 */
class PlayerCorrectAnswersByCategory extends Statistic
{
    private $playerId;
    private $categoryId;
    private static $_preloadData;

    public function __construct($playerId, $categoryId)
    {
        $this->playerId = $playerId;
        $this->categoryId = $categoryId;
    }

    public static function preload($appId)
    {
        self::$_preloadData = [];
        $data = GameQuestionAnswer::join('users', 'users.id', '=', 'game_question_answers.user_id')
            ->join('game_questions', 'game_questions.id', '=', 'game_question_answers.game_question_id')
            ->join('questions', 'questions.id', '=', 'game_questions.question_id')
            ->where('users.app_id', $appId)
            ->where('result', 1)
            ->groupBy(['user_id', 'category_id'])
            ->select(DB::RAW('COUNT(*) as count, game_question_answers.user_id as user_id, category_id'))
            ->getQuery()->get();
        foreach ($data as $entry) {
            if (! isset(self::$_preloadData[$entry->category_id])) {
                self::$_preloadData[$entry->category_id] = [];
            }
            self::$_preloadData[$entry->category_id][$entry->user_id] = $entry->count;
        }
    }

    /**
     * Returns the amount of answers the user answered correctly.
     *
     * @return int
     */
    protected function getValue()
    {
        if (self::$_preloadData) {
            return isset(self::$_preloadData[$this->categoryId][$this->playerId]) && isset(self::$_preloadData[$this->categoryId][$this->playerId]) ? self::$_preloadData[$this->categoryId][$this->playerId] : 0;
        }

        // Fetch the amount of correct answers the user gave
        $correctAnswers = GameQuestionAnswer::ofUser($this->playerId)
                                            ->whereHas('gameQuestion.question', function ($q) {
                                                $q->where('questions.category_id', $this->categoryId);
                                            })
                                            ->where('result', 1)
                                            ->count();

        return $correctAnswers;
    }

    protected function getCacheKey()
    {
        return 'player-correct-answers-'.$this->playerId.'-category-'.$this->categoryId;
    }

    protected function getCacheTags()
    {
        return ['player-'.$this->playerId];
    }
}
