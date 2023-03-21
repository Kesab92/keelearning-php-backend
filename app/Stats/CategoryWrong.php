<?php

namespace App\Stats;

use App\Models\Game;
use App\Models\GameQuestionAnswer;
use App\Models\QuestionAnswer;

/**
 * Calculates how often a category's question has been answered wrong.
 */
class CategoryWrong extends Statistic
{
    /**
     * @var
     */
    private $categoryId;
    private static $_preloadData;

    public function __construct($categoryId)
    {
        $this->categoryId = $categoryId;
    }

    protected function getCacheDuration()
    {
        return 60 * 48;
    }

    public static function preload($appId)
    {
        self::$_preloadData = GameQuestionAnswer::join('game_questions', 'game_question_answers.game_question_id', '=', 'game_questions.id')
            ->join('questions', 'game_questions.question_id', '=', 'questions.id')
            ->where('questions.app_id', $appId)
            ->where('result', 0)
            ->groupBy('questions.category_id')
            ->select(\DB::raw('COUNT(*) as c'), \DB::raw('questions.category_id as id'))
            ->pluck('c', 'id');
    }

    /**
     * Returns how often a question of this category has been answered wrong.
     *
     * @return int
     */
    protected function getValue()
    {
        if (self::$_preloadData) {
            return isset(self::$_preloadData[$this->categoryId]) ? self::$_preloadData[$this->categoryId] : 0;
        }

        // Fetch the amount of wrong answers the user gave
        $wrongAnswers = GameQuestionAnswer::
                                            whereHas('gameQuestion.question', function ($q) {
                                                $q->where('questions.category_id', $this->categoryId);
                                            })
                                            ->where('result', 0)
                                            ->count();

        return $wrongAnswers;
    }

    protected function getCacheKey()
    {
        return 'category-wrong-'.$this->categoryId;
    }

    protected function getCacheTags()
    {
        return ['category-'.$this->categoryId];
    }
}
