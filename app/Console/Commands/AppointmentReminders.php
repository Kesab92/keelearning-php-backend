<?php

namespace App\Console\Commands;

use App\Mail\Mailer;
use App\Models\Appointments\Appointment;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AppointmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Looking for appointment reminders and sends email to the user';

    /**
     * Look up for reminders and sends mail with queue.
     */
    public function handle()
    {
        $this->info('Checking for due appointment reminders');
        $mailer = app(Mailer::class);
        $appointments = Appointment
            ::where('is_draft', 0)
            ->where('is_cancelled', 0)
            ->whereNotNull('send_reminder_at')
            ->where('send_reminder_at', '<=', Carbon::now())
            ->get();

        $this->info('Appointment reminders found: ' . $appointments->count());

        foreach ($appointments as $appointment) {
            $participants = $appointment->getParticipants();
            $participants->load([
                'app',
                'tags',
            ]);

            $this->line('Sent the reminders for the appointment #'.$appointment->id);

            foreach ($participants as $participant) {
                $mailer->sendAppointmentReminder($participant, $appointment);
            }

            $appointment->send_reminder_at = null;
            $appointment->save();
        }
    }
}
