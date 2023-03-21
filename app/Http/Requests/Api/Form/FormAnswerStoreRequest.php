<?php

namespace App\Http\Requests\Api\Form;

use App\Models\Forms\Form;
use App\Rules\KeysIn;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FormAnswerStoreRequest extends FormRequest
{

    public function authorize()
    {
        $form = Form::find($this->form_id);

        if(!$form || $form->app_id !== user()->app_id) {
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
        $form = Form::findOrFail($this->form_id);
        $requiredFormFields = $form->fields->where('is_required')->pluck('id');

        return [
            'answerFields' => [
                'required',
                'array',
                new KeysIn($form->fields->pluck('id'))
            ],
            'answerFields.*' => function ($attribute, $value, $fail) use ($requiredFormFields) {
                $fieldId = (int)Str::after($attribute, 'answerFields.');
                if($requiredFormFields->contains($fieldId) && empty($value)) {
                    $fail('The answer for field "'.$fieldId.'" is required.');
                }
            }
        ];
    }
}
