<?php

namespace App\Console\Commands;

use App\Jobs\CompetitionStartNotification;
use App\Models\Competition;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendCompetitionInvitation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'competitions:invite';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends invitation after a competition has been started.';

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
        $this->info('Start sending competition invitations');
        $competitions = Competition::whereNotNull('start_at')
                   ->where('start_at', '<=', Carbon::now())
                   ->whereNull('notification_sent_at')
                   ->get();

        $this->info('Found '.$competitions->count().' active competitions');
        if ($competitions->count()) {
            $this->output->progressStart($competitions->count());
        }

        foreach ($competitions as $competition) {
            if (Carbon::now() > $competition->getEndDate()) {
                continue;
            }

            $competition->notification_sent_at = Carbon::now();
            $competition->save();
            CompetitionStartNotification::dispatch($competition);
            $this->output->progressAdvance();
        }

        if ($competitions->count()) {
            $this->output->progressFinish();
        }
        $this->info('Finished sending invitations');
    }
}
