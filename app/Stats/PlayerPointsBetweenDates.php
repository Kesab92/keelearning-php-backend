<?php

namespace App\Stats;

use App\Models\Game;
use App\Models\GamePoint;

/**
 * Calculates the amount of answers the user answered correctly for a category.
 */
class PlayerPointsBetweenDates extends Statistic
{
    /**
     * @var
     */
    private $playerId;
    /**
     * @var
     */
    private $from;
    /**
     * @var
     */
    private $to;

    public function __construct($playerId, $from, $to)
    {
        $this->playerId = $playerId;
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * Returns the amount of answers the user answered correctly.
     *
     * @return int
     */
    protected function getValue()
    {
        // Fetch the amount of correct answers the user gave
        return GamePoint::where('user_id', $this->playerId)
                        ->whereBetween('created_at', [$this->from, $this->to])
                        ->sum('amount');
    }

    protected function getCacheKey()
    {
        return 'player-points-'.$this->playerId.'-'.$this->from.'-'.$this->to;
    }

    protected function getCacheTags()
    {
        return ['player-'.$this->playerId];
    }
}
