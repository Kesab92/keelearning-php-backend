<?php

namespace App\Console\Commands;

use App\Models\App;
use App\Services\StatsEngine;
use App\Stats\QuestionCorrect;
use App\Stats\QuestionWrong;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class CacheQuestionStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:cache:questions {appid?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Caches all question stats';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($appid = $this->argument('appid')) {
            $apps = new Collection([App::find($appid)]);
        } else {
            $apps = App::all();
        }
        $this->info('Start generating question stats');
        $bar = $this->output->createProgressBar($apps->count());
        foreach ($apps as $app) {
            $statsEngine = new StatsEngine($app->id);
            $statsEngine->getQuestionList();
            $statsEngine->getQuestionPercentages();
            $bar->advance();
        }
        $bar->finish();
    }
}
