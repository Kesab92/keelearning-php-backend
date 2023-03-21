<?php

namespace App\Console\Commands;

use App\Models\App;
use App\Services\StatsEngine;
use Cache;
use Illuminate\Console\Command;
use Log;

class CacheAPIPlayerStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:stats:cache:players {appid?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerates all player api stats';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($appid = $this->argument('appid')) {
            $apps = [App::find($appid)];
        } else {
            $apps = App::all();
        }
        foreach ($apps as $app) {
            $this->info(date('Y-m-d H:i:s').' Start api stats generation of '.$app->name);

            $statsEngine = new StatsEngine($app->id);
            $results = $statsEngine->getRawAPIPlayerList();
            Cache::put('player-api-stats-'.$app->id, $results, 60 * 60 * 24);

            $this->info(date('Y-m-d H:i:s').' Done with api player stats generation of '.$app->name);

            $quizTeamResults = $statsEngine->getRawQuizTeamApiList();
            Cache::put('quiz-team-api-stats-'.$app->id, $quizTeamResults, 60 * 60 * 24);

            $this->info(date('Y-m-d H:i:s').' Done with api quiz team stats generation of '.$app->name);
        }
    }
}
