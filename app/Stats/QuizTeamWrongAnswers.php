<?php

namespace App\Stats;

use App\Models\GameQuestionAnswer;
use App\Models\QuizTeamMember;
use DB;

/**
 * Calculates the amount of answers the quiz team's users answered incorrectly.
 */
class QuizTeamWrongAnswers extends Statistic
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
     * Returns the amount of answers the quiz team's users answered incorrectly.
     *
     * @return int
     */
    protected function getValue()
    {
        $quizTeamMemberIds = QuizTeamMember::where('quiz_team_id', $this->quizTeamId)
            ->pluck('user_id');
        $count = GameQuestionAnswer::whereIn('user_id', $quizTeamMemberIds)
            ->where('result', 0)
            ->select(DB::RAW('COUNT(*) as wrongAnswers'))
            ->first();

        return $count->wrongAnswers;
    }

    protected function getCacheKey()
    {
        return 'quiz-team-wrong-answers-'.$this->quizTeamId;
    }

    protected function getCacheTags()
    {
        return ['quiz-team-'.$this->quizTeamId];
    }
}
