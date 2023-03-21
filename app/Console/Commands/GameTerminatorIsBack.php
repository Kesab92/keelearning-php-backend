<?php

namespace App\Console\Commands;

use App\Models\App;
use App\Services\Terminator;
use Illuminate\Console\Command;

class GameTerminatorIsBack extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'terminate:game';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'The Terminator is back and finishes all games, where the current round was not finished within 24 or 72 hours';

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
        $apps = App::all();

        $this->info('Checking for timed out games...');
        /** @var App $app */
        foreach ($apps as $app) {
            Terminator::seekAndFinishGame($app, $this);
        }
        $this->info('Finished games');
    }
}
