<?php

namespace App\Http\Requests\PublicApi;

use Illuminate\Foundation\Http\FormRequest;

abstract class PublicApiListFormRequest extends FormRequest
{
    const MAXIMUM_PER_PAGE = 500;
    const DEFAULT_PER_PAGE = 50;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return true;
    }

    /**
     * Gets the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'page' => 'integer|min:0',
            'perPage' => 'integer|min:1|max:'.self::MAXIMUM_PER_PAGE
        ];
    }

    /**
     * Gets messages for validation rules.
     *
     * @return string[]
     */
    public function messages()
    {
        return [
            'page.min' => 'Invalid parameter "page". It has to be at least 0.',
            'perPage.min' => 'Invalid parameter "perPage". It has to be at least 1.',
            'perPage.max' => 'Invalid parameter "perPage". You can request at most '.self::MAXIMUM_PER_PAGE .' items in a single request.',
        ];
    }

    protected function prepareForValidation() {
        $perPage = (int)$this->perPage;
        if($perPage === 0) {
            $perPage = self::DEFAULT_PER_PAGE;
        }
        $this->merge([
            'page' => (int)$this->page,
            'perPage' => $perPage,
        ]);
    }
}
