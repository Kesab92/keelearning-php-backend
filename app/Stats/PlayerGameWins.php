<?php

namespace App\Stats;

use App\Models\Game;
use DB;

/**
 * Calculates the amount of games the user won.
 */
class PlayerGameWins extends Statistic
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
        self::$_preloadData = Game::join('users', 'users.id', '=', 'games.player1_id')
            ->where('users.app_id', $appId)
            ->where('status', Game::STATUS_FINISHED)
            ->whereNotNull('winner')
            ->groupBy('winner')
            ->select(DB::RAW('COUNT(*) as count, winner'))
            ->pluck('count', 'winner');
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

        $won = 0;
        /** @var Game[] $games */
        $games = Game::ofUser($this->playerId)
                     ->where('status', '=', Game::STATUS_FINISHED)
                     ->get();

        foreach ($games as $game) {
            if ($game->getWinner() == $this->playerId) {
                $won++;
            }
        }

        return $won;
    }

    protected function getCacheKey()
    {
        return 'player-game-wins-'.$this->playerId;
    }

    protected function getCacheTags()
    {
        return ['player-'.$this->playerId];
    }
}
