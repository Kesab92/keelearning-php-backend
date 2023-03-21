<?php

namespace App\Mail;

use App\Models\Appointments\Appointment;
use App\Models\User;
use App\Services\QueuePriority;
use App\Traits\CustomMail;
use App\Traits\HasAppointmentTags;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class AppointmentStartDateWasUpdated extends KeelearningNotification
{
    use Queueable, SerializesModels;
    use CustomMail;
    use HasAppointmentTags;

    const START_DATE_WAS_UPDATED = 1;
    const START_DATE_WAS_CANCELLED = 2;

    public static array $availableTags = [
        'appointment-link',
        'appointment-type',
        'appointment-change-kind',
        'appointment-start-date',
    ];
    public static array $requiredTags = [
        'appointment-name',
    ];

    /**
     * AppointmentStartDateWasUpdated constructor.
     * @param Appointment $appointment
     * @param User $user
     * @param int $changeKind
     */
    public function __construct(Appointment $appointment, User $user, int $changeKind)
    {
        parent::__construct();

        $appointment->setLanguage($user->getLanguage());

        $appProfile = $user->getAppProfile();
        $appointmentLink = $appProfile->app_hosted_at . '/appointments/' . $appointment->id;

        $changeKindTexts = [
            AppointmentStartDateWasUpdated::START_DATE_WAS_UPDATED => __('appointment.was_updated', [], $user->getLanguage()),
            AppointmentStartDateWasUpdated::START_DATE_WAS_CANCELLED => __('appointment.was_cancelled', [], $user->getLanguage()),
        ];

        $pushNotificationData = $this->getAppointmentTags($appointment, $user);
        $pushNotificationData['appointment-change-kind'] = $changeKindTexts[$changeKind];

        $this->app = $user->app;
        $this->data = [
            'appointment-link' => $appointmentLink,
            'appointment-change-kind' => $changeKindTexts[$changeKind],
        ];
        $this->queue = QueuePriority::LOW;
        $this->recipient = $user;
        $this->pushNotificationMessage = __('notifications.appointment_start_date_was_updated', $pushNotificationData, $user->getLanguage());
        $this->pushNotificationData = ['open_url' => '/appointments/' . $appointment->id];
        $this->addTagData($this->getAppointmentTags($appointment, $user));
    }
}
