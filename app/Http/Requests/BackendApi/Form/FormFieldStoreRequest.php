<?php

namespace App\Http\Requests\BackendApi\Form;

use App\Models\Forms\FormField;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FormFieldStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'position' => [
                'required',
                'integer',
            ],
            'type' => [
                'required',
                Rule::in(FormField::ALL_TYPES),
            ],
        ];
    }
}
