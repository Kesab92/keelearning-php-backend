<?php

namespace App\Stats;

use App\Models\Game;
use App\Models\GameQuestionAnswer;
use Carbon\Carbon;
use DB;

/**
 * Calculates the amount of questions the user answered correctly.
 */
class PlayerAnswerHistory extends Statistic
{
    /**
     * @var
     */
    private $playerId;

    public function __construct($playerId)
    {
        $this->playerId = $playerId;
    }

    /**
     * Returns the amount of answers the user answered correctly.
     *
     * @return int
     */
    protected function getValue()
    {

        // Fetch the amount of correct answers the user gave
        $correctAnswers = GameQuestionAnswer::ofUser($this->playerId)
                                            ->where('result', 1)
                                            ->select('created_at', DB::raw('COUNT(*) as c'))
                                            ->groupBy(DB::raw('DATE(created_at)'))
                                            ->having('created_at', '>=', Carbon::now()->subDays(14)->format('Y-m-d 00:00:00'))
                                            ->get();

        $history = [];
        foreach ($correctAnswers as $answer) {
            $history[date('d.m.Y', strtotime($answer->created_at))] = $answer->c;
        }

        // Build the result array by taking the last 14 days and adding 0 values where neccessary
        $result = [];
        $now = Carbon::now();
        while ($now >= Carbon::now()->subDays(14)) {
            $dateFormatted = $now->format('d.m.Y');
            if (! isset($history[$dateFormatted])) {
                $result[$dateFormatted] = 0;
            } else {
                $result[$dateFormatted] = $history[$dateFormatted];
            }
            $now->subDay();
        }

        $result = array_reverse($result);

        return $result;
    }

    protected function getCacheKey()
    {
        return 'player-answer-history-'.$this->playerId;
    }

    protected function getCacheTags()
    {
        return ['player-'.$this->playerId];
    }
}
