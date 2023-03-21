<?php

namespace App\Http\Requests\BackendApi;

use App\Models\Courses\Course;
use App\Services\Courses\CoursesEngine;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CourseUpdateRequest extends FormRequest
{
    private $course;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // redundant since we have a middleware on the controller
        if(!Auth::user()->hasRight('courses-edit')) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'duration_type' => function ($attribute, $value, $fail) {
                if (!in_array($value, [Course::DURATION_TYPE_FIXED, Course::DURATION_TYPE_DYNAMIC])) {
                    $fail('duration_type is wrong.');
                }
            },
            'participation_duration_type' => function ($attribute, $value, $fail) {
                if (!in_array($value, [
                    Course::PARTICIPATION_DURATION_DAYS,
                    Course::PARTICIPATION_DURATION_WEEKS,
                    Course::PARTICIPATION_DURATION_MONTHS,
                ])) {
                    $fail('participation_duration_type is wrong.');
                }
            },
            'repetition_interval_type' => function ($attribute, $value, $fail) {
                if (!in_array($value, [Course::INTERVAL_WEEKLY, Course::INTERVAL_MONTHLY])) {
                    $fail('repetition_interval_type is wrong.');
                }
            },
            'time_limit_type' => function ($attribute, $value, $fail) {
                if (!in_array($value, [Course::INTERVAL_WEEKLY, Course::INTERVAL_MONTHLY])) {
                    $fail('time_limit_type is wrong.');
                }
            },
            'is_repeating' => function ($attribute, $value, $fail) {
                if ($value && !$this->course->is_template) {
                    $fail('Only template can be repeating.');
                }
            },
        ];
    }
    protected function prepareForValidation() {

        $coursesEngine=app(CoursesEngine::class);
        $this->course = $coursesEngine->getCourse($this->course_id, Auth::user());
    }
}
