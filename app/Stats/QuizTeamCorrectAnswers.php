<?php

namespace App\Stats;

use App\Models\GameQuestionAnswer;
use App\Models\QuizTeamMember;
use DB;

/**
 * Calculates the amount of answers the quiz team's users answered correctly.
 */
class QuizTeamCorrectAnswers extends Statistic
{
    /**
     * @var
     */
    private $quizTeamId;

    protected function getCacheDuration()
    {
        return 60 * 48;
    }

    public function __construct($quizTeamId)
    {
        $this->quizTeamId = $quizTeamId;
    }

    /**
     * Returns the amount of answers the quiz team's users answered correctly.
     *
     * @return int
     */
    protected function getValue()
    {
        $quizTeamUserIds = QuizTeamMember::where('quiz_team_id', $this->quizTeamId)
            ->pluck('user_id');
        $count = GameQuestionAnswer::whereIn('user_id', $quizTeamUserIds)
            ->where('result', 1)
            ->select(DB::RAW('COUNT(*) as correctAnswers'))
            ->first();

        return $count->correctAnswers;
    }

    protected function getCacheKey()
    {
        return 'quiz-team-correct-answers-'.$this->quizTeamId;
    }

    protected function getCacheTags()
    {
        return ['quiz-team-'.$this->quizTeamId];
    }
}
