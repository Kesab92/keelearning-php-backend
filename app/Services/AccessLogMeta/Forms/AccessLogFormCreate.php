<?php

namespace App\Services\AccessLogMeta\Forms;

use App\Models\Appointments\Appointment;
use App\Models\Forms\Form;
use App\Services\AccessLogMeta\AccessLogMeta;

class AccessLogFormCreate implements AccessLogMeta
{
    /**
     * @var null
     */
    protected $form = null;

    /**
     * AccessLogQuestionCreate constructor.
     * @param Form $form
     */
    public function __construct(Form $form)
    {
        $this->form = $form;
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        return $this->form;
    }

    /**
     * @param $meta
     * @return string
     */
    public static function displayMeta($meta)
    {
        return view('access-logs.types.forms.create', [
            'meta' => $meta,
        ]);
    }
}
