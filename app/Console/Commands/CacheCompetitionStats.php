<?php

namespace App\Console\Commands;

use App\Models\App;
use App\Models\Competition;
use App\Stats\PlayerCorrectAnswersByCategoryBetweenDates;
use DB;
use Illuminate\Console\Command;

class CacheCompetitionStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:cache:competitions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Caches all player stats';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Regenerate all competitions which are active or have been active in the last week
        Competition::where(DB::raw('DATE_ADD(created_at,INTERVAL duration DAY)'), '>=', DB::raw('DATE_ADD(NOW(),INTERVAL 7 DAY)'))
                   ->get()
                   ->each(function ($competition) {
                       $members = $competition->members();
                       if ($members) {
                           $members->map(function ($user) use ($competition) {
                               $user->stats = [
                                   'answersCorrect' => (new PlayerCorrectAnswersByCategoryBetweenDates($user->id, $competition->id, $competition->start_at, $competition->getEndDate()))->fetch(),
                               ];
                           });
                       }
                   });
    }
}
