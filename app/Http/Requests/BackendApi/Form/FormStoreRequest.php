<?php

namespace App\Http\Requests\BackendApi\Form;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FormStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'tags' => [
                'array',
            ],
            'tags.*' => Rule::exists('tags', 'id')->where(function ($query) {
                return $query->where('app_id', appId());
            }),
            'title' => [
                'required',
                'min:3',
            ],
        ];
    }
}
