<?php

namespace App\Mail;

use App\Models\Appointments\Appointment;
use App\Models\User;
use App\Services\Appointments\AppointmentEngine;
use App\Services\QueuePriority;
use App\Traits\CustomMail;
use App\Traits\HasAppointmentTags;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class NewAppointment extends KeelearningNotification
{
    use Queueable, SerializesModels;
    use CustomMail{
        build as buildBase;
    }
    use HasAppointmentTags;

    public static array $availableTags = [
        'appointment-link',
        'appointment-type',
        'appointment-details',
        'appointment-start-date',
    ];
    public static array $requiredTags = [
        'appointment-name',
    ];

    private Appointment $appointment;

    /**
     * NewAppointment constructor.
     * @param Appointment $appointment
     * @param User $user
     */
    public function __construct(Appointment $appointment, User $user)
    {
        parent::__construct();

        $appointment->setLanguage($user->getLanguage());

        $appProfile = $user->getAppProfile();
        $appointmentLink = $appProfile->app_hosted_at . '/appointments/' . $appointment->id;
        $appointmentDetails = __('appointment.date', [], $user->getLanguage()) . ": " . $appointment->start_date->format('d.m.Y') . "\n";
        $appointmentDetails .= __('appointment.start', [], $user->getLanguage()) . ": " . $appointment->start_date->format('H:i') . "\n";
        $appointmentDetails .= __('appointment.end', [], $user->getLanguage()) . ": " . $appointment->end_date->format('H:i');

        if ($appointment->location) {
            $appointmentDetails .= "\n" . __('appointment.location') . ": " . $appointment->location;
        }
        if ($appointment->description) {
            $appointmentDetails .= "\n\n" . strip_tags(html_entity_decode($appointment->description));
        }
        $this->app = $user->app;
        $this->appointment = $appointment;
        $this->data = [
            'appointment-link' => $appointmentLink,
            'appointment-details' => $appointmentDetails,
        ];
        $this->queue = QueuePriority::LOW;
        $this->recipient = $user;
        $this->pushNotificationMessage = __('notifications.new_appointment', $this->getAppointmentTags($appointment, $user), $user->getLanguage());
        $this->pushNotificationData = ['open_url' => '/appointments/' . $appointment->id];
        $this->addTagData($this->getAppointmentTags($appointment, $user));
    }

    /**
     * Builds the mail.
     * @return NewAppointment
     */
    public function build()
    {
        $filename = Str::slug($this->appointment->name) . '.ics';

        return $this->buildBase()
            ->attach($this->createAttachment($filename));
    }

    /**
     * Creates an attachment for the email.
     * @param $filename
     */
    private function createAttachment(string $filename)
    {
        $appointmentEngine = app(AppointmentEngine::class);

        $filepath = sys_get_temp_dir() . '/' . $filename;
        file_put_contents($filepath, $appointmentEngine->getIcsEventContent($this->appointment));

        return $filepath;
    }
}
