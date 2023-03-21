<?php

namespace App\Stats;

use App\Models\Game;
use App\Models\GamePoint;
use DB;

/**
 * Calculates the amount of points the player has.
 */
class PlayerPoints extends Statistic
{
    /**
     * @var
     */
    private $playerId;
    private static $_preloadData;

    public function __construct($playerId)
    {
        $this->playerId = $playerId;
    }

    public static function preload($appId)
    {
        self::$_preloadData = GamePoint::join('users', 'users.id', '=', 'game_points.user_id')
            ->where('users.app_id', $appId)
            ->groupBy('user_id')
            ->select(DB::RAW('COUNT(*) as count, game_points.user_id as user_id'))
            ->pluck('count', 'user_id');
    }

    /**
     * Returns the amount of points the user has.
     *
     * @return int
     */
    protected function getValue()
    {
        if (self::$_preloadData) {
            return isset(self::$_preloadData[$this->playerId]) ? self::$_preloadData[$this->playerId] : 0;
        }

        return GamePoint::where('user_id', $this->playerId)->sum('amount');
    }

    protected function getCacheKey()
    {
        return 'player-points-'.$this->playerId;
    }

    protected function getCacheTags()
    {
        return ['player-'.$this->playerId];
    }
}
