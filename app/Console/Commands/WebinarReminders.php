<?php

namespace App\Console\Commands;

use App\Mail\Mailer;
use App\Models\Webinar;
use App\Services\WebinarEngine;
use Carbon\Carbon;
use Illuminate\Console\Command;

class WebinarReminders extends Command
{
    const REMINDER_LIMIT = 50; // max number of reminders per webinar to send
    const REMINDER_MINUTES = 15; // how many minutes before start to send reminders
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webinars:reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends webinar reminders';

    private Mailer $mailer;
    private WebinarEngine $webinarEngine;

    public function __construct(Mailer $mailer, WebinarEngine $webinarEngine)
    {
        parent::__construct();
        $this->mailer = $mailer;
        $this->webinarEngine = $webinarEngine;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Checking for webinars starting in '.self::REMINDER_MINUTES.' minutes.');
        $fromTime = Carbon::now();
        $toTime = Carbon::now()->addMinutes(self::REMINDER_MINUTES);
        $webinars = Webinar::where('send_reminder', true)
            ->whereNull('reminder_sent_at')
            ->whereBetween('starts_at', [$fromTime, $toTime])
            ->get();
        if (! $webinars->count()) {
            $this->info('No webinars need reminding.');

            return;
        }
        $this->line($webinars->count().' webinars need reminders.');
        foreach ($webinars as $webinar) {
            $this->sendWebinarMails($webinar);
            $webinar->reminder_sent_at = Carbon::now();
            $webinar->save();
        }
        $this->line('Reminders sent.');
    }

    public function sendWebinarMails(Webinar $webinar)
    {
        $mailsLeft = self::REMINDER_LIMIT;
        $this->info('Sending reminders for webinar #'.$webinar->id);

        $additionalUsers = $webinar->additionalUsers()
            ->whereNotNull('user_id')
            ->with('user')
            ->get()
            ->pluck('user');
        foreach ($additionalUsers as $additionalUser) {
            $this->mailer->sendWebinarReminder($additionalUser, $webinar);
            $mailsLeft -= 1;
            if ($mailsLeft <= 0) {
                return;
            }
        }

        $externalUsers = $webinar->additionalUsers()->whereNull('user_id')->get();
        foreach ($externalUsers as $externalUser) {
            $this->mailer->sendWebinarReminderExternal($externalUser);
            $mailsLeft -= 1;
            if ($mailsLeft <= 0) {
                return;
            }
        }

        $this->webinarEngine->getWebinarTagUsersQuery($webinar)
            ->chunk(100, function ($users) use ($mailsLeft, $webinar) {
                foreach ($users as $user) {
                    $this->mailer->sendWebinarReminder($user, $webinar);
                    $mailsLeft -= 1;
                    if ($mailsLeft <= 0) {
                        return false; // break out of chunking
                    }
                }
            });
    }
}
