<?php

namespace App\Console\Commands;

use App\Models\App;
use App\Services\StatsEngine;
use App\Stats\CategoryCorrect;
use App\Stats\CategoryWrong;
use Illuminate\Console\Command;

class CacheCategoriesStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:cache:categories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Caches all category stats';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $apps = App::all();
        $this->info('Start generating category stats');
        $bar = $this->output->createProgressBar($apps->count());
        foreach (App::all() as $app) {
            $statsEngine = new StatsEngine($app->id);
            $statsEngine->getCategoryList();
            $statsEngine->categories();
            $bar->advance();
        }
        $bar->finish();
    }
}
