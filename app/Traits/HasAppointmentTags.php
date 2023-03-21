<?php

namespace App\Traits;

use App\Models\Appointments\Appointment;
use App\Models\User;

trait HasAppointmentTags
{

    public function getAppointmentTags(Appointment $appointment, User $user):array {
        $typeTexts = [
            Appointment::TYPE_ONLINE =>  __('appointment.type_online', [], $user->getLanguage()),
            Appointment::TYPE_IN_PERSON =>  __('appointment.type_in_person', [], $user->getLanguage()),
        ];

        return [
            'appointment-name' => $appointment->name,
            'appointment-type' => $typeTexts[$appointment->type],
            'appointment-start-date' => $appointment->start_date->format('d.m.Y H:i'),
        ];
    }
}
