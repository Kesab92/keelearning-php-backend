<?php

namespace App\Http\Requests\BackendApi\Appointment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AppointmentStoreRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->hasRight('appointments-edit');
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
                'required',
                'min:3',
            ],
            'start_date' => [
                'required',
                'date_format:Y-m-d H:i',
            ],
            'end_date' => [
                'required',
                'date_format:Y-m-d H:i',
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
