<?php

namespace App\Console\Commands;

use App\Models\Competition;
use App\Services\Referee;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CompetitionReferee extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'competition:finish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'The referee checks if there is a competition that already ended and tells the members the results';

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
        $competitions = Competition
            ::whereRaw('start_at <= NOW()')
            ->whereRaw('DATE_ADD(start_at,INTERVAL duration+2 DAY) >= NOW()')
            ->get();

        /** @var Competition $competition */
        foreach ($competitions as $competition) {
            Referee::seekAndFinishCompetition($competition->id);
        }
    }
}
