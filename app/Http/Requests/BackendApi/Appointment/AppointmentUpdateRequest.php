<?php

namespace App\Http\Requests\BackendApi\Appointment;

use App\Models\Appointments\Appointment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AppointmentUpdateRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->hasRight('appointments-edit') && Appointment::where('app_id', appId())->find($this->id);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => [
                'sometimes',
                'required',
                'min:3',
            ],
            'type' => [
                'sometimes',
                'required',
                Rule::in([
                    Appointment::TYPE_ONLINE,
                    Appointment::TYPE_IN_PERSON,
                ]),
            ],
            'is_draft' => [
                'sometimes',
                'required',
                'boolean',
            ],
            'has_reminder' => [
                'sometimes',
                'required',
                'boolean',
            ],
            'reminder_time' => [
                'sometimes',
                'nullable',
                'integer',
            ],
            'reminder_unit_type' => [
                'sometimes',
                'nullable',
                Rule::in([
                    Appointment::REMINDER_TIME_UNIT_MINUTES,
                    Appointment::REMINDER_TIME_UNIT_HOURS,
                    Appointment::REMINDER_TIME_UNIT_DAYS,
                ]),
            ],
            'location' => [
                'sometimes',
                'nullable',
                'min:3',
            ],
            'start_date' => [
                'sometimes',
                'required',
                'date_format:Y-m-d H:i',
            ],
            'end_date' => [
                'sometimes',
                'required',
                'date_format:Y-m-d H:i',
            ],
            'published_at' => [
                'sometimes',
                'nullable',
                'date',
            ],
            'tags' => [
                'array',
            ],
            'tags.*' => Rule::exists('tags', 'id')->where(function ($query) {
                return $query->where('app_id', appId());
            }),
        ];
    }
}
