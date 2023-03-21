<?php

namespace App\Http\Requests\BackendApi\QuizTeam;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuizTeamStoreRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
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
            'name' => [
                'required',
                'min:3',
                Rule::unique('quiz_teams')->where(function ($query) {
                    $query->where('app_id', appId());
                    $query->where('name', $this->name);
                }),
            ],
        ];
    }

    protected function prepareForValidation() {
        $this->merge([
            'name' => utrim($this->name),
        ]);
    }
}
