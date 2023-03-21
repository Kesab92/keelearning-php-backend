<?php

namespace App\Stats;

use App\Models\GameQuestionAnswer;

/**
 * Calculates how often a question has been answered correctly.
 */
class QuestionCorrectByPlayers extends Statistic
{
    /**
     * @var
     */
    private $players;
    private $questionId;
    private static $_preloadData;

    protected function getCacheDuration()
    {
        return 60 * 48;
    }

    public function __construct($questionId, $players)
    {
        $this->questionId = $questionId;
        $this->players = $players;
    }

    public static function preload($appId, $players)
    {
        self::$_preloadData = GameQuestionAnswer::whereIn('user_id', $players)
        ->join('game_questions', 'game_question_answers.game_question_id', '=', 'game_questions.id')
        ->join('questions', 'game_questions.question_id', '=', 'questions.id')
        ->where('questions.app_id', $appId)
        ->where('result', 1)
        ->groupBy('game_questions.question_id')
        ->select(\DB::raw('COUNT(*) as c'), \DB::raw('game_questions.question_id as id'))
        ->pluck('c', 'id');
    }

    /**
     * Returns how often the question has been answered correctly.
     *
     * @return int
     */
    protected function getValue()
    {
        if (self::$_preloadData) {
            return isset(self::$_preloadData[$this->questionId]) ? self::$_preloadData[$this->questionId] : 0;
        }
        // Fetch the amount of correct answers the user gave
        $correctAnswers = GameQuestionAnswer::whereIn('user_id', $this->players)
                                            ->whereHas('gameQuestion', function ($q) {
                                                $q->where('question_id', $this->questionId);
                                            })
                                            ->where('result', 1)
                                            ->count();

        return $correctAnswers;
    }

    protected function getCacheKey()
    {
        return 'question-correct-by-players'.$this->questionId.'-'.implode(';', $this->players->toArray());
    }

    protected function getCacheTags()
    {
        return ['question-'.$this->questionId];
    }
}
