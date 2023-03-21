<?php

namespace App\Jobs;

use App\Mail\Mailer;
use App\Models\Competition;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mail;

class CompetitionStartNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     * @var int
     */
    public $tries = 1;

    /**
     * @var Competition|null
     */
    protected $competition = null;

    /**
     * @var Mailer
     */
    protected $mailer = null;

    /**
     * Create a new job instance.
     *
     * @param Competition $competition
     */
    public function __construct(Competition $competition)
    {
        $this->competition = $competition;
        $this->mailer = app(Mailer::class);
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        foreach ($this->competition->members() as $user) {
            $this->mailer->sendCompetitionInvite($user, $this->competition);
        }
    }

    public function tags()
    {
        return ['appid:'.$this->competition->app_id];
    }
}
