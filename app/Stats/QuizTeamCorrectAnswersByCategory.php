<?php

namespace App\Stats;

use App\Models\GameQuestionAnswer;
use App\Models\QuizTeamMember;
use DB;

/**
 * Calculates the amount of answers the quiz team's users answered correctly for a category.
 */
class QuizTeamCorrectAnswersByCategory extends Statistic
{
    /**
     * @var
     */
    private $quizTeamId;
    private $categoryId;

    protected function getCacheDuration()
    {
        return 60 * 48;
    }

    public function __construct($quizTeamId, $categoryId)
    {
        $this->quizTeamId = $quizTeamId;
        $this->categoryId = $categoryId;
    }

    /**
     * Returns the amount of answers the quiz team's users answered correctly.
     *
     * @return int
     */
    protected function getValue()
    {
        $quizTeamMemberIds = QuizTeamMember::where('quiz_team_id', $this->quizTeamId)
                                 ->pluck('user_id');
        $correctAnswers = GameQuestionAnswer::join('game_questions', 'game_questions.id', '=', 'game_question_answers.game_question_id')
            ->join('questions', 'questions.id', '=', 'game_questions.question_id')
            ->whereIn('user_id', $quizTeamMemberIds)
            ->where('result', 1)
            ->where('category_id', $this->categoryId)
            ->select(DB::RAW('COUNT(*) as correctAnswers'))
            ->first();

        return $correctAnswers->correctAnswers;
    }

    protected function getCacheKey()
    {
        return 'quiz-team-correct-answers-'.$this->quizTeamId.'-category-'.$this->categoryId;
    }

    protected function getCacheTags()
    {
        return ['quiz-team-'.$this->quizTeamId];
    }
}
