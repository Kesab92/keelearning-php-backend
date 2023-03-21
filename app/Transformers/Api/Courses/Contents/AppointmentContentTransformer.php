<?php

namespace App\Transformers\Api\Courses\Contents;

use App\Models\Appointments\Appointment;
use App\Transformers\AbstractTransformer;
use Illuminate\Support\Facades\URL;

class AppointmentContentTransformer extends AbstractTransformer
{

    public function transform($model)
    {
        if (!$model) {
            return;
        }
        /** @var Appointment $appointment */
        $appointment = $model->relatable;

        return [
            'cover_image_url' => $appointment->cover_image_url,
            'created_at' => $appointment->created_at->toDateTimeString(),
            'description' => $appointment->description,
            'end_date' => $appointment->end_date->toDateTimeString(),
            'ics_url' => URL::temporarySignedRoute(
                'appointmentIcsFile',
                now()->addHours(12),
                [
                    'appointmentId' => $appointment->id,
                    'language' => user()->language,
                ],
            ),
            'id' => $appointment->id,
            'is_cancelled' => $appointment->is_cancelled,
            'location' => $appointment->location,
            'name' => $model->title ?: $appointment->name,
            'start_date' => $appointment->start_date->toDateTimeString(),
            'type' => $appointment->type,
            'updated_at' => $appointment->updated_at->toDateTimeString(),
        ];
    }
}

