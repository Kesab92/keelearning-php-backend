<?php

namespace App\Console\Commands;

use App\Models\App;
use App\Services\StatsEngine;
use Cache;
use Illuminate\Console\Command;

class CacheStrongPlayers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:cache:strongplayers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Caches the strong players of the last 7 days';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $apps = App::all();
        $this->info('Start fetching strong players');
        $bar = $this->output->createProgressBar($apps->count());
        foreach (App::all() as $app) {
            $statsEngine = new StatsEngine($app->id);
            $results = $statsEngine->getStrongPlayersRaw();
            Cache::put('strong-players-'.$app->id, $results, 60 * 60 * 48);
            $bar->advance();
        }
        $bar->finish();
    }
}
