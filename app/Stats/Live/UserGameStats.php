<?php
namespace App\Stats\Live;

use App\Models\Game;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class UserGameStats {
    public function attach(Collection $data) {
        if(!$data->count()) {
            return;
        }
        $appId = $data->first()['app_id'];
        $botIds = User::where('app_id', $appId)
            ->withoutGlobalScope('human')
            ->where('is_bot', '>', 0)
            ->pluck('id');
        $player1Data = Game
            ::where('app_id', $appId)
            ->whereIn('player1_id', $data->pluck('id'))
            ->select([
                'player1_id',
                \Db::raw('MAX(created_at) as last_game'),
                \DB::raw('COUNT(*) as games'),
                \DB::raw('SUM(CASE WHEN (winner = player1_id and player2_id NOT IN (' . $botIds->implode(',') . ')) THEN 1 ELSE 0 END) as human_wins'),
                \DB::raw('SUM(CASE WHEN (status = ' . Game::STATUS_FINISHED . ' and player2_id NOT IN (' . $botIds->implode(',') . ')) THEN 1 ELSE 0 END) as human_finished'),
                \DB::raw('SUM(CASE WHEN (player2_id NOT IN (' . $botIds->implode(',') . ')) THEN 1 ELSE 0 END) as human_games'),
            ])
            ->groupBy('player1_id')
            ->get()
            ->keyBy('player1_id');

        $player2Data = Game
            ::where('app_id', $appId)
            ->whereIn('player2_id', $data->pluck('id'))
            ->select([
                'player2_id',
                \Db::raw('MAX(created_at) as last_game'),
                \DB::raw('COUNT(*) as games'),
                \DB::raw('SUM(CASE WHEN status = ' . Game::STATUS_FINISHED . ' THEN 1 ELSE 0 END) as human_finished'),
                \DB::raw('SUM(CASE WHEN winner = player2_id THEN 1 ELSE 0 END) as human_wins'),
            ])
            ->groupBy('player2_id')
            ->get()
            ->keyBy('player2_id');

        $data->transform(function($user) use ($player1Data, $player2Data) {
            $player1 = collect($player1Data->get($user['id'], []));
            $player2 = collect($player2Data->get($user['id'], []));

            $user['last_game'] = max($player1->get('last_game'), $player2->get('last_game'));
            $user['games'] = $player1->get('games', 0) + $player2->get('games', 0);
            $user['human_wins'] = $player1->get('human_wins', 0) + $player2->get('human_wins', 0);
            $humanFinished = $player1->get('human_finished', 0) + $player2->get('human_finished', 0);
            if($humanFinished === 0) {
                $user['human_win_percentage'] = 0;
            } else {
                $user['human_win_percentage'] = round($user['human_wins'] / $humanFinished, 2);
            }
            $user['human_games'] = $player1->get('human_games', 0) + $player2->get('games', 0);

            return $user;
        });
    }
}
