<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointments\Appointment;
use App\Services\Appointments\AppointmentEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Response;

class AppointmentsController extends Controller
{
    /**
     * Returns a list of all appointments for the current user.
     *
     * @param AppointmentEngine $appointmentEngine
     * @return JsonResponse
     */
    public function getAllAppointments(AppointmentEngine $appointmentEngine): JsonResponse
    {
        $appointments = $appointmentEngine
            ->getUserAppointments(user())
            ->map(function($appointment) {
                return $this->formatAppointment($appointment);
            });
        return Response::json([
            'appointments' => $appointments,
        ]);
    }

    public function getIcsFile(int $appointmentId, string $language, AppointmentEngine $appointmentEngine) {
        $appointment = Appointment
            ::visible()
            ->findOrFail($appointmentId)
            ->setLanguage($language);


        $filename = Str::slug($appointment->name);

        return response($appointmentEngine->getIcsEventContent($appointment))
            ->withHeaders([
                'Content-type: text/calendar; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '.ics"',
            ]);
    }

    /**
     * Formats an appointment for the frontend app
     *
     * @param Appointment $appointment
     * @return array
     */
    private function formatAppointment(Appointment $appointment): array
    {
        return [
            'id'              => $appointment->id,
            'cover_image_url' => $appointment->cover_image_url,
            'created_at'      => $appointment->created_at->toDateTimeString(),
            'description'     => $appointment->description,
            'end_date'        => $appointment->end_date->toDateTimeString(),
            'is_cancelled'    => $appointment->is_cancelled,
            'location'        => $appointment->location,
            'name'            => $appointment->name,
            'start_date'      => $appointment->start_date->toDateTimeString(),
            'type'            => $appointment->type,
            'updated_at'      => $appointment->updated_at->toDateTimeString(),
            'ics_url' => URL::temporarySignedRoute(
                'appointmentIcsFile',
                now()->addHours(12),
                [
                    'appointmentId' => $appointment->id,
                    'language' => user()->language,
                ],
            ),
        ];
    }
}
