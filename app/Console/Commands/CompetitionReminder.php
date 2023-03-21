<?php

namespace App\Console\Commands;

use App\Mail\Mailer;
use App\Models\App;
use App\Models\Competition;
use App\Stats\PlayerCorrectAnswersByCategoryBetweenDates;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CompetitionReminder extends Command
{
    const REMINDER_DAYS = [
        2,
        5,
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'competitions:remind';

    private $mailer;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remind users about running competitions';

    /**
     * Create a new command instance.
     *
     * @param Mailer $mailer
     */
    public function __construct(Mailer $mailer)
    {
        parent::__construct();
        $this->mailer = $mailer;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $apps = App::all();

        /** @var App $app */
        foreach ($apps as $app) {
            $runningCompetitions = Competition::where('app_id', $app->id)
                ->where('start_at', '<', Carbon::now())
                ->where(\DB::raw('DATE_ADD(start_at, INTERVAL duration + 1 DAY)'), '>', Carbon::now())
                ->get();

            $count = $runningCompetitions->count();

            $this->info('Checking CompetitionReminders for '.$count.' running competitions of app #'.$app->id);
            $bar = $this->output->createProgressBar($count);

            foreach ($runningCompetitions as $competition) {
                if (! $competition->hasStartDate()) {
                    continue;
                }
                $daysSince = $competition->start_at->diffInDays();
                if (! in_array($daysSince, self::REMINDER_DAYS)) {
                    continue;
                }
                $users = $competition->members();
                if (! $users) {
                    continue;
                }
                $users = $users->map(function ($user) use ($competition) {
                    $user->correct_answers = (new PlayerCorrectAnswersByCategoryBetweenDates($user->id, $competition->category_id, $competition->start_at, $competition->getEndDate()))->fetch();

                    return $user;
                })->sortByDesc('correct_answers');
                $rankingNumber = 1;
                foreach ($users as $user) {
                    $this->mailer->sendCompetitionReminder($user, $competition, $rankingNumber);
                    $rankingNumber += 1;
                }
                $bar->advance();
            }
            $bar->finish();
            $this->line('');
        }
    }
}
