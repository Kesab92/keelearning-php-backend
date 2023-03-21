<?php

namespace App\Http\Requests\BackendApi\Question;

use App\Models\Category;
use App\Models\Question;
use App\Rules\CategoryTagRights;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class QuestionUpdateRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->hasEditPermissions();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $question = Question::find($this->id);

        return [
            'answertime' => [
                'nullable',
                'numeric',
                'min:1',
            ],
            'category_id' => [
                'required',
                Rule::exists(Category::class, 'id')->where('app_id', appId()),
                new CategoryTagRights(Auth::user(), $question->category_id)
            ],
        ];
    }

    /**
     * Checks if the user has access to question updating.
     */
    private function hasEditPermissions(): bool {
        if(!Auth::user()->hasRight('questions-edit')) {
            return false;
        }
        return true;
    }

    protected function prepareForValidation() {
        if(isset($this->answertime)) {
            $answerTime = $this->answertime;
            if ($answerTime === '') {
                $answerTime = null;
            }

            $this->merge(['answertime' => $answerTime]);
        }
    }
}
