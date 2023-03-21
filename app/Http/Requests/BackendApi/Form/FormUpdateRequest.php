<?php

namespace App\Http\Requests\BackendApi\Form;

use App\Models\Appointments\Appointment;
use App\Models\ContentCategories\ContentCategory;
use App\Models\Forms\Form;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class FormUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'categories' => [
                'array',
            ],
            'categories.*' => Rule::exists('content_categories', 'id')->where(function ($query) {
                return $query->where('app_id', appId())->where('type', ContentCategory::TYPE_FORMS);
            }),
            'tags' => [
                'array',
            ],
            'tags.*' => Rule::exists('tags', 'id')->where(function ($query) {
                return $query->where('app_id', appId());
            }),
            'title' => [
                'sometimes',
                'required',
                'min:3',
            ],
        ];
    }
}
