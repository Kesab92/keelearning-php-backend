<?php

namespace App\Console\Commands;

use App\Models\App;
use App\Models\Game;
use App\Services\Terminator;
use Illuminate\Console\Command;
use Sentry;

class RoundTerminatorIsBack extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'terminate:round';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'The Terminator is back and finishes incomplete rounds';

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
        $this->info('The Terminator is back!');
        // Retrieve all active games and get their last
        $activeGames = Game::active()
            ->get();

        /** @var Game $activeGame */
        foreach ($activeGames as $activeGame) {
            try {
                Terminator::seekAndFinishRound($activeGame, $this);
            } catch (\Exception $e) {
                Sentry::captureException($e);
            }
        }
    }
}
