<?php

namespace App\Console\Commands;

use App\Models\App;
use App\Models\User;
use App\Services\StatsEngine;
use App\Stats\PlayerAnswerHistory;
use Illuminate\Console\Command;

class CachePlayerStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:cache:players';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Caches all player stats';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        foreach (App::all() as $app) {
            $statsEngine = new StatsEngine($app->id);
            $statsEngine->getQuizPlayersList();
            $statsEngine->getTrainingPlayersList();
        }
        $this->info('Cached all player lists');

        User::query()->select('id')->chunk(1000, function ($users) {
            foreach ($users as $user) {
                (new PlayerAnswerHistory($user->id))->fetch();
            }
            $this->info('Processed 1000 users');
        });

        $this->info('Cached all players');
    }
}
