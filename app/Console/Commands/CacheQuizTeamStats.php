<?php

namespace App\Console\Commands;

use App\Models\App;
use App\Services\AppSettings;
use App\Services\StatsEngine;
use App\Stats\PlayerCorrectAnswers;
use App\Stats\PlayerCorrectAnswersByCategory;
use App\Stats\PlayerGameWins;
use Cache;
use Illuminate\Console\Command;

class CacheQuizTeamStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:cache:quizteams {appid?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Caches all quiz team stats';

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
            $statsEngine = new StatsEngine($app->id);
            $results = $statsEngine->getRawQuizTeamList();
            Cache::put('quiz-team-api-stats-'.$app->id, $results, 60 * 60 * 24);
        }
    }
}
