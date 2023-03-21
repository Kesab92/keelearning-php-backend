<?php

namespace App\Console\Commands;

use App\Jobs\SendAppointmentPublishNotifications;
use App\Models\Appointments\Appointment;
use App\Models\LearningMaterial;
use App\Models\News;
use App\Services\LearningMaterialEngine;
use App\Services\NewsEngine;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendContentNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sendcontentnotifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'sends content notifications for today';

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
        $newsEngine = app(NewsEngine::class);
        $news = News::where('send_notification', true)
            ->whereNotNull('published_at')
            ->whereDate('published_at', '<=', Carbon::today())
            ->where('notification_sent_at', null)
            ->get();
        if ($news->count()) {
            $this->info('Sending News notifications.');
            $this->output->progressStart($news->count());
            foreach ($news as $entry) {
                $newsEngine->sendNotification($entry);
                $this->output->progressAdvance();
            }
            $this->output->progressFinish();
            $this->info('Sent News notifications.');
        } else {
            $this->info('No News scheduled for today.');
        }

        $learningMaterialEngine = app(LearningMaterialEngine::class);
        $learningMaterial = LearningMaterial::where('send_notification', true)
            ->whereDate('published_at', '<=', Carbon::today())
            ->where('notification_sent_at', null)
            ->where('visible', 1)
            ->get();
        if ($learningMaterial->count()) {
            $this->info('Sending Learning Material notifications.');
            $this->output->progressStart($learningMaterial->count());
            foreach ($learningMaterial as $entry) {
                $learningMaterialEngine->sendNotification($entry);
                $this->output->progressAdvance();
            }
            $this->output->progressFinish();
            $this->info('Sent Learning Material notifications.');
        } else {
            $this->info('No Learning Material scheduled for today.');
        }

        $appointments = Appointment
            ::where('send_notification', true)
            ->where('is_draft', 0)
            ->where('is_cancelled', 0)
            ->whereNotNull('published_at')
            ->whereDate('published_at', '<=', Carbon::today())
            ->whereNull('last_notification_sent_at')
            ->get();
        if ($appointments->count()) {
            $this->info('Sending appointments notifications.');
            $this->output->progressStart($appointments->count());
            foreach ($appointments as $appointment) {
                $appointment->last_notification_sent_at = Carbon::now();
                $appointment->save();

                SendAppointmentPublishNotifications::dispatch($appointment);

                $this->output->progressAdvance();
            }
            $this->output->progressFinish();
            $this->info('Sent appointments notifications.');
        } else {
            $this->info('No appointments scheduled for today.');
        }
    }
}
