<?php

namespace App\Console\Commands;

use App\Mail\Mailer;
use Illuminate\Console\Command;

class Emailtest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emailtest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display an inspiring quote';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $mailer = new Mailer();
        $mailer->sendAppInvitation(1, 'p.mohr@sopamo.de', 1, 'foopwd');
        //$mailer->sendAppAbortInformation(70020);
        //$mailer->sendAppReminder(1,1,1);
        //$this->sendCompetitionResults();
        //$mailer->sendInvitation(70020);
        //$mailer->sendReminder(70020,1);
    }
}
