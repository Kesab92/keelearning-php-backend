<?php

namespace App\Stats;

use App\Models\Game;
use App\Models\QuizTeamMember;

/**
 * Calculates the amount of games played by the members of the quiz team.
 */
class QuizTeamGames extends Statistic
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
     * Returns the amount of games.
     *
     * @return int
     */
    protected function getValue()
    {
        $quizTeamMemberIds = QuizTeamMember::where('quiz_team_id', $this->quizTeamId)
            ->pluck('user_id');

        return Game::whereIn('player1_id', $quizTeamMemberIds)
            ->orWhereIn('player2_id', $quizTeamMemberIds)
            ->where('status', Game::STATUS_FINISHED)
            ->count();
    }

    protected function getCacheKey()
    {
        return 'quiz-team-games-'.$this->quizTeamId;
    }

    protected function getCacheTags()
    {
        return ['quiz-team-'.$this->quizTeamId];
    }
}
