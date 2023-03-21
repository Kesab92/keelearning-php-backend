<?php

namespace App\Stats;

use DB;

/**
 * Calculates which players were the strongest in the last 7 days.
 */
class StrongPlayers extends Statistic
{
    private $appId;

    public function __construct($appId)
    {
        $this->appId = $appId;
    }

    protected function getCacheDuration()
    {
        return 60 * 24;
    }

    /**
     * Returns the strongest players & their win rate.
     *
     * @return int
     */
    protected function getValue()
    {
        $players = [];
        $since = date('Y-m-d', strtotime('-1 week'));

        $gamesAsChallenger =
            DB::table('games')
                ->where('app_id', $this->appId)
                ->where('updated_at', '>=', $since)
                ->where('status', 0)
                ->where('winner', '>', 0)
                ->select([
                    'player1_id as user_id',
                    DB::raw('COUNT(*) as count'),
                ])
                ->groupBy([
                    'user_id',
                ])
                ->get();
        foreach ($gamesAsChallenger as $game) {
            if (! isset($players[$game->user_id])) {
                $players[$game->user_id] = [
                    'id'      => $game->user_id,
                    'games'   => 0,
                    'losses'  => 0,
                    'score'   => 0,
                    'winrate' => 0,
                    'wins'    => 0,
                ];
            }
            $players[$game->user_id]['games'] += $game->count;
        }

        $gamesAsChallenged =
            DB::table('games')
                ->where('app_id', $this->appId)
                ->where('updated_at', '>=', $since)
                ->where('status', 0)
                ->where('winner', '>', 0)
                ->select([
                    'player2_id as user_id',
                    DB::raw('COUNT(*) as count'),
                ])
                ->groupBy([
                    'user_id',
                ])
                ->get();
        foreach ($gamesAsChallenged as $game) {
            if (! isset($players[$game->user_id])) {
                $players[$game->user_id] = [
                    'id'      => $game->user_id,
                    'games'   => 0,
                    'losses'  => 0,
                    'score'   => 0,
                    'winrate' => 0,
                    'wins'    => 0,
                ];
            }
            $players[$game->user_id]['games'] += $game->count;
        }

        $gamesWon =
            DB::table('games')
                ->where('app_id', $this->appId)
                ->where('updated_at', '>=', $since)
                ->where('status', 0)
                ->where('winner', '>', 0)
                ->select([
                    'winner as user_id',
                    DB::raw('COUNT(*) as count'),
                ])
                ->groupBy([
                    'user_id',
                ])
                ->get();

        foreach ($gamesWon as $game) {
            if (! isset($players[$game->user_id])) {
                // this should never be happening unless something is wrong with the DB
                $players[$game->user_id] = [
                    'id'      => $game->user_id,
                    'games'   => 0,
                    'losses'  => 0,
                    'score'   => 0,
                    'winrate' => 0,
                    'wins'    => 0,
                ];
            }
            $players[$game->user_id]['wins'] = $game->count;
            if ($players[$game->user_id]['games']) {
                $players[$game->user_id]['losses'] = $players[$game->user_id]['games'] - $players[$game->user_id]['wins'];
                $players[$game->user_id]['winrate'] = $players[$game->user_id]['wins'] / $players[$game->user_id]['games'];
                $players[$game->user_id]['score'] = calculateScore($players[$game->user_id]['wins'], $players[$game->user_id]['games']);
            }
        }

        $players = array_filter($players, function ($player) {
            return $player['wins'] > 0;
        });

        usort($players, function ($a, $b) {
            if ($a['score'] == $b['score']) {
                return 0;
            }

            return ($a['score'] > $b['score']) ? -1 : 1;
        });

        return $players;
    }

    protected function getCacheKey()
    {
        return 'strong-players-'.$this->appId;
    }

    protected function getCacheTags()
    {
        return ['app-'.$this->appId];
    }
}
