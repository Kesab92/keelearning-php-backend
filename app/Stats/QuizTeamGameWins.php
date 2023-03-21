<?php

namespace App\Stats;

use App\Models\Game;
use App\Models\QuizTeamMember;
use DB;

/**
 * Calculates the amount of games the user won.
 */
class QuizTeamGameWins extends Statistic
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
     * Returns the amount of games the user won.
     *
     * @return int
     */
    protected function getValue()
    {
        $quizTeamMemberIds = QuizTeamMember::where('quiz_team_id', $this->quizTeamId)
                                 ->pluck('user_id');
        $wins = Game::whereIn('winner', $quizTeamMemberIds)
            ->where('status', Game::STATUS_FINISHED)
            ->select(DB::RAW('COUNT(*) as wins'))
            ->first();

        return $wins->wins;
    }

    protected function getCacheKey()
    {
        return 'quiz-team-game-wins-'.$this->quizTeamId;
    }

    protected function getCacheTags()
    {
        return ['quiz-team-'.$this->quizTeamId];
    }
}
