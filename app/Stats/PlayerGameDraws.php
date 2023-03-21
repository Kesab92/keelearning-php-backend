<?php

namespace App\Stats;

use App\Models\Game;
use DB;

/**
 * Calculates the amount of games the user won.
 */
class PlayerGameDraws extends Statistic
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
        $p1 = Game::join('users', 'users.id', '=', 'games.player1_id')
            ->where('users.app_id', $appId)
            ->where('status', Game::STATUS_FINISHED)
            ->where('winner', 0)
            ->groupBy('player1_id')
            ->select(DB::RAW('COUNT(*) as count, player1_id'))
            ->pluck('count', 'player1_id');
        $p2 = Game::join('users', 'users.id', '=', 'games.player1_id')
            ->where('users.app_id', $appId)
            ->where('status', Game::STATUS_FINISHED)
            ->where('winner', 0)
            ->groupBy('player2_id')
            ->select(DB::RAW('COUNT(*) as count, player2_id'))
            ->pluck('count', 'player2_id');
        self::$_preloadData = [];
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
     * Returns the amount of games the user won.
     *
     * @return int
     */
    protected function getValue()
    {
        if (self::$_preloadData) {
            return isset(self::$_preloadData[$this->playerId]) ? self::$_preloadData[$this->playerId] : 0;
        }
        $draws = 0;
        /** @var Game[] $games */
        $games = Game::ofUser($this->playerId)
                     ->where('status', '=', Game::STATUS_FINISHED)
                     ->get();

        foreach ($games as $game) {
            if ($game->getWinner() == 0) {
                $draws++;
            }
        }

        return $draws;
    }

    protected function getCacheKey()
    {
        return 'player-game-draws-'.$this->playerId;
    }

    protected function getCacheTags()
    {
        return ['player-'.$this->playerId];
    }
}
