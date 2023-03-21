<?php

namespace App\Models\Appointments;

use App\Models\KeelearningModel;
use App\Traits\Duplicatable;

/**
 * @mixin IdeHelperAppointmentTranslation
 */
class AppointmentTranslation extends KeelearningModel
{
    use Duplicatable;

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
