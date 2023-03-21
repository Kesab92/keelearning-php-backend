<?php

namespace App\Stats;

use App\Models\Game;
use App\Models\User;
use DB;

/**
 * Calculates the amount of games the user won, excluding bot games.
 */
class PlayerGameWinsBetweenDates extends Statistic
{
    /**
     * @var
     */
    private $playerId;
    private $from;
    private $to;
    private static $_preloadData;

    public function __construct($playerId, $from, $to)
    {
        $this->playerId = $playerId;
        $this->from = $from;
        $this->to = $to;
    }

    public static function preload($appId, $from, $to)
    {
        $botIds = User::where('app_id', $appId)
            ->withoutGlobalScope('human')
            ->where('is_bot', '>', 0)
            ->pluck('id');
        self::$_preloadData = Game
            ::whereBetween('games.created_at', [$from, $to])
            ->where('games.app_id', $appId)
            ->where('games.status', Game::STATUS_FINISHED)
            ->whereNotNull('winner')
            ->whereNotIn('player1_id', $botIds)
            ->whereNotIn('player2_id', $botIds)
            ->groupBy('winner')
            ->select(DB::RAW('COUNT(*) as count, winner'))
            ->pluck('count', 'winner');
    }

    /**
     * Returns the amount of games the user won, minus bot games.
     *
     * @return int
     */
    protected function getValue()
    {
        if (self::$_preloadData) {
            return isset(self::$_preloadData[$this->playerId]) ? self::$_preloadData[$this->playerId] : 0;
        }

        /* @var Game[] $games */
        return Game::where('winner', $this->playerId)
                     ->whereBetween('created_at', [$this->from, $this->to])
                     ->where('status', '=', Game::STATUS_FINISHED)
                     ->whereHas('player1', function ($query) {
                         $query->where('is_bot', 0);
                     })
                     ->whereHas('player2', function ($query) {
                         $query->where('is_bot', 0);
                     })
                     ->count();
    }

    protected function getCacheKey()
    {
        return 'player-game-wins-between'.$this->playerId.'-'.$this->from.'-'.$this->to;
    }

    protected function getCacheTags()
    {
        return ['player-'.$this->playerId];
    }
}
