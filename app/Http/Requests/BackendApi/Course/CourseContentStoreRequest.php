<?php

namespace App\Http\Requests\BackendApi\Course;

use App\Models\Courses\CourseContent;
use App\Services\AppSettings;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CourseContentStoreRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->hasRight('courses-edit');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $appSettings = app(AppSettings::class, ['appId' => appId()]);

        $availableTypes = [
            CourseContent::TYPE_CERTIFICATE,
            CourseContent::TYPE_CHAPTER,
            CourseContent::TYPE_QUESTIONS,
            CourseContent::TYPE_TODOLIST,
        ];

        if($appSettings->getValue('module_learningmaterials', true)) {
            $availableTypes[] = CourseContent::TYPE_LEARNINGMATERIAL;
        }
        if($appSettings->getValue('module_forms', true)) {
            $availableTypes[] = CourseContent::TYPE_FORM;
        }
        if($appSettings->getValue('module_appointments', true)) {
            $availableTypes[] = CourseContent::TYPE_APPOINTMENT;
        }

        return [
            'chapter' => [
                'sometimes',
                'required',
                'integer',
            ],
            'position' => [
                'required',
                'integer',
            ],
            'type' => [
                'required',
                Rule::in($availableTypes),
            ],
        ];
    }
}
