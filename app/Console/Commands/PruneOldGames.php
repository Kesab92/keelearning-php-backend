<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;

class PruneOldGames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'games:prune {appid} {date_until}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes all games of an app up until the specified date (inclusive)';

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
        $app = \App\Models\App::find($appId);

        if (! $app) {
            $this->error('Could not find app with id #'.$appId);

            return;
        }

        if (! $this->confirm('Is "'.$app->name.'" the app from which you want to delete games?')) {
            return;
        }

        $dateUntil = Carbon::parse($this->argument('date_until'))->endOfDay();
        if (! $this->confirm('Delete all games which started up until '.$dateUntil.'?')) {
            return;
        }

        $games = $app->games()->where('created_at', '<=', $dateUntil)->get();
        $this->info('Deleting '.$games->count().' games…');
        $bar = $this->output->createProgressBar($games->count());
        foreach ($games as $game) {
            $game->safeRemove();
            $bar->advance();
        }
        $bar->finish();
        $this->line('');
        $this->info('All done!');
    }
}
