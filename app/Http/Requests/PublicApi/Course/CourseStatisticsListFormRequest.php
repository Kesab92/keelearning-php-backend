<?php

namespace App\Http\Requests\PublicApi\Course;

use App\Http\Requests\PublicApi\PublicApiListFormRequest;
use App\Models\Courses\Course;
use Auth;

class CourseStatisticsListFormRequest extends PublicApiListFormRequest {

    public function rules()
    {
        $rules = parent::rules();
        $rules['orderBy'] = 'sometimes|nullable|in:started_at_asc,started_at_desc,updated_at_asc,updated_at_desc,finished_at_asc,finished_at_desc';
        return $rules;
    }

    public function authorize()
    {
        $course = Course::find($this->resourceId);

        if(!$course || $course->app_id !== Auth::user()->app_id) {
            return false;
        }

        return true;
    }
}
