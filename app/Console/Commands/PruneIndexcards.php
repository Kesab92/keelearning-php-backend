<?php

namespace App\Console\Commands;

use App\Models\App;
use App\Models\IndexCard;
use App\Models\LearnBoxCard;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PruneIndexcards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'indexcards:prune {appid}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes all indecards of an app';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $appId = $this->argument('appid');
        /** @var App $app */
        $app = App::find($appId);

        if (! $app) {
            $this->error('Could not find app with id #'.$appId);

            return;
        }

        if (! $this->confirm('Is "'.$app->name.'" the app from which you want to delete indexcards?')) {
            return;
        }

        $indexcards = IndexCard::where('app_id', $app->id)->get();
        if (! $this->confirm('Do you want to delete '.$indexcards->count().' indexcards?')) {
            return;
        }
        $this->info('Deleting '.$indexcards->count().' indexcards…');
        $bar = $this->output->createProgressBar($indexcards->count());
        foreach ($indexcards as $indexcard) {
            $indexcard->safeRemove();
            $bar->advance();
        }
        $bar->finish();
        $this->line('');
        $this->info('All done!');
    }
}
