<?php

namespace App\Mail;

use App\Models\Appointments\Appointment;
use App\Models\User;
use App\Services\QueuePriority;
use App\Traits\CustomMail;
use App\Traits\HasAppointmentTags;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class AppointmentReminder extends KeelearningNotification
{
    use Queueable, SerializesModels;
    use CustomMail;
    use HasAppointmentTags;

    public static array $availableTags = [
        'appointment-type',
        'appointment-location',
        'appointment-description',
        'appointment-start-date',
    ];
    public static array $requiredTags = [
        'appointment-name',
        'appointment-date',
        'appointment-time',
    ];

    /**
     * AppointmentReminder constructor.
     * @param Appointment $appointment
     * @param User $user
     */
    public function __construct(Appointment $appointment, User $user)
    {
        parent::__construct();

        $appointment->setLanguage($user->getLanguage());

        $pushNotificationData = $this->getAppointmentTags($appointment, $user);
        $pushNotificationData['appointment-date'] = $appointment->start_date->format('d.m.Y');
        $pushNotificationData['appointment-time'] = $appointment->start_date->format('H:i');

        $this->app = $user->app;
        $this->data = [
            'appointment-location' => $appointment->location,
            'appointment-description' => strip_tags(html_entity_decode($appointment->description)),
            'appointment-date' => $appointment->start_date->format('d.m.Y'),
            'appointment-time' => $appointment->start_date->format('H:i'),
        ];
        $this->queue = QueuePriority::LOW;
        $this->recipient = $user;
        $this->pushNotificationMessage = __('notifications.appointment_reminder', $pushNotificationData, $user->getLanguage());
        $this->pushNotificationData = ['open_url' => '/appointments/' . $appointment->id];
        $this->addTagData($this->getAppointmentTags($appointment, $user));
    }
}
