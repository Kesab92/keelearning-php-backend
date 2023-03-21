<?php

namespace App\Stats;

use App\Models\Game;
use DB;

/**
 * Calculates the amount of games the user lost.
 */
class PlayerGameLosses extends Statistic
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
            ->where('winner', '>', 0)
            ->groupBy('looser')
            ->select(DB::RAW('COUNT(*) as count, CASE WHEN winner != player1_id THEN player1_id WHEN winner != player2_id THEN player2_id END as looser'))
            ->pluck('count', 'looser');
    }

    /**
     * Returns the amount of games the user lost.
     *
     * @return int
     */
    protected function getValue()
    {
        if (self::$_preloadData) {
            return isset(self::$_preloadData[$this->playerId]) ? self::$_preloadData[$this->playerId] : 0;
        }
        $lost = 0;
        /** @var Game[] $games */
        $games = Game::ofUser($this->playerId)
                     ->where('status', '=', Game::STATUS_FINISHED)
                     ->get();

        foreach ($games as $game) {
            $winner = $game->getWinner();
            if ($winner == null) {
                continue;
            }
            if ($winner > 0 && $winner != $this->playerId) {
                $lost++;
            }
        }

        return $lost;
    }

    protected function getCacheKey()
    {
        return 'player-game-losses-'.$this->playerId;
    }

    protected function getCacheTags()
    {
        return ['player-'.$this->playerId];
    }
}
