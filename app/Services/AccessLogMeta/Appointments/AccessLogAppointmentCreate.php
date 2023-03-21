<?php

namespace App\Services\AccessLogMeta\Appointments;

use App\Models\Appointments\Appointment;
use App\Services\AccessLogMeta\AccessLogMeta;

class AccessLogAppointmentCreate implements AccessLogMeta
{
    /**
     * @var null
     */
    protected $appointment = null;

    /**
     * AccessLogQuestionCreate constructor.
     * @param Appointment $appointment
     */
    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        return $this->appointment;
    }

    /**
     * @param $meta
     * @return string
     */
    public static function displayMeta($meta)
    {
        return view('access-logs.types.appointments.create', [
            'meta' => $meta,
        ]);
    }
}
