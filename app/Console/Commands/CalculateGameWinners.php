<?php

namespace App\Console\Commands;

use App\Models\Game;
use Illuminate\Console\Command;

class CalculateGameWinners extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:calculatewinners {appId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute cleanup commands for lingomint';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->argument('appId')) {
            $games = Game::where('app_id', $this->argument('appId'))->get();
        } else {
            $games = Game::all();
        }
        foreach ($games as $game) {
            /* @var Game $game */
            $game->getWinner();
        }
    }
}
