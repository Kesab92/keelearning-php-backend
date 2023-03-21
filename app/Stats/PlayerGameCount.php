<?php

namespace App\Stats;

use App\Models\Game;
use DB;

/**
 * Calculates the amount of games the user played.
 */
class PlayerGameCount extends Statistic
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
        self::$_preloadData = [];
        $p1 = Game::where('app_id', $appId)
            ->whereIn('status', [Game::STATUS_FINISHED, Game::STATUS_CANCELED])
            ->groupBy('player1_id')
            ->select(DB::RAW('COUNT(*) as count, player1_id'))
            ->pluck('count', 'player1_id');
        $p2 = Game::where('app_id', $appId)
            ->whereIn('status', [Game::STATUS_FINISHED, Game::STATUS_CANCELED])
            ->groupBy('player2_id')
            ->select(DB::RAW('COUNT(*) as count, player2_id'))
            ->pluck('count', 'player2_id');
        foreach ([$p1, $p2] as $data) {
            foreach ($data as $playerId => $count) {
                if (! isset(self::$_preloadData[$playerId])) {
                    self::$_preloadData[$playerId] = 0;
                }
                self::$_preloadData[$playerId] += $count;
            }
        }
    }

    /**
     * Returns the amount of games the user played.
     *
     * @return int
     */
    protected function getValue()
    {
        if (self::$_preloadData) {
            return isset(self::$_preloadData[$this->playerId]) ? self::$_preloadData[$this->playerId] : 0;
        }

        return Game::ofUser($this->playerId)
                   ->whereIn('status', [Game::STATUS_FINISHED, Game::STATUS_CANCELED])
                   ->count();
    }

    protected function getCacheKey()
    {
        return 'player-game-count-'.$this->playerId;
    }

    protected function getCacheTags()
    {
        return ['player-'.$this->playerId];
    }
}
