<?php


namespace App\Services\AccessLogMeta\Appointments;

use App\Services\AccessLogMeta\AccessLogMeta;

class AccessLogAppointmentDelete implements AccessLogMeta
{
    /**
     * Deleted object
     * @var null
     */
    protected $appointment = null;

    public function __construct($appointment)
    {
        $this->appointment = $appointment;
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        return [
            'appointment_id' => $this->appointment->id,
            'appointment_name' => $this->appointment->name,
        ];
    }

    /**
     * @param $meta
     * @return string
     */
    public static function displayMeta($meta)
    {
        return view('access-logs.types.appointments.delete', [
            'meta' => $meta
        ]);
    }
}
