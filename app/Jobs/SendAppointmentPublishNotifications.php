<?php

namespace App\Jobs;
use App\Mail\Mailer;
use App\Models\Appointments\Appointment;
use App\Services\QueuePriority;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendAppointmentPublishNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     * @var int
     */
    public int $tries = 1;

    /**
     * Displays all receiving users.
     */
    protected ?Appointment $appointment = null;

    /**
     * Sends the mail.
     */
    protected ?Mailer $mailer = null;

    /**
     * Create a new job instance.
     *
     * @param Appointment $appointment
     */
    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
        $this->mailer = app(Mailer::class);
        $this->queue = QueuePriority::LOW;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $participants = $this->appointment->getParticipants();
        $participants->load([
            'app',
            'tags'
        ]);

        foreach ($participants as $participant) {
            $this->mailer->sendNewAppointment($participant, $this->appointment);
        }
    }
}
