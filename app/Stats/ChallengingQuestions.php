<?php

namespace App\Stats;

use App\Models\Category;
use App\Models\GameQuestionAnswer;
use DB;

/**
 * Calculates the difficulty score of all questions, sorting them from most to least difficult.
 */
class ChallengingQuestions extends Statistic
{
    private $appId;

    public function __construct($appId)
    {
        $this->appId = $appId;
    }

    protected function getCacheDuration()
    {
        return 60 * 24;
    }

    /**
     * Returns the most challenging questions of the last 7 days.
     *
     * @return int
     */
    protected function getValue()
    {
        $categories = Category::ofApp($this->appId)->pluck('id');
        $results = [];
        foreach ($categories as $category) {
            $results[$category] = $this->getValueForCategory($category);
        }

        return $results;
    }

    protected function getValueForCategory($categoryId)
    {

        // Fetch the amounts of right and wrong answers per question
        $answerInformation =
            GameQuestionAnswer::where('questions.app_id', $this->appId)
                ->where('game_question_answers.created_at', '>=', date('Y-m-d', strtotime('-1 week')))
                ->where('questions.category_id', $categoryId)
                ->whereIn('result', [0, 1])
                ->join('game_questions', 'game_questions.id', '=', 'game_question_answers.game_question_id')
                ->join('questions', 'questions.id', '=', 'game_questions.question_id')
                ->select([
                    'game_questions.question_id as id',
                    DB::raw('SUM(game_question_answers.result = 1) as correct'),
                    DB::raw('SUM(game_question_answers.result = 0) as wrong'),
                    DB::raw('COUNT(game_question_answers.id) as amount'),
                ])
                ->having('amount', '>', 0)
                ->groupBy('id')
                ->get();

        $sanitizedAnswers = [];
        foreach ($answerInformation as $answer) {
            $sum = $answer['correct'] + $answer['wrong'];
            $sanitizedAnswers[] = [
                'answercount' => $sum,
                'failrate'    => $answer['wrong'] / $sum,
                'id'          => $answer['id'],
                'score'       => calculateScore($answer['wrong'], $sum),
            ];
        }

        usort($sanitizedAnswers, function ($a, $b) {
            if ($a['score'] == $b['score']) {
                return 0;
            }

            return ($a['score'] > $b['score']) ? -1 : 1;
        });

        // return first 3 results
        return array_slice($sanitizedAnswers, 0, 3);
    }

    protected function getCacheKey()
    {
        return 'challenging-questions-'.$this->appId;
    }

    protected function getCacheTags()
    {
        return ['app-'.$this->appId];
    }
}
