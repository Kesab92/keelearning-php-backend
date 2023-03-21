<?php

namespace App\Http\Requests\PublicApi\Tag;

use App\Http\Requests\PublicApi\PublicApiStoreFormRequest;
use Illuminate\Validation\Rule;

class TagStoreFormRequest extends PublicApiStoreFormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */

    public function authorize() {
        return true;
    }

    public function rules()
    {
        return [
            'label' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tags')->where(function ($query) {
                    $query->where('app_id', appId());
                }),
            ],
        ];
    }

    /**
     * Gets messages for validation rules.
     *
     * @return string[]
     */
    public function messages() {
        return [
            'label.required' => 'Parameter "label" is required.',
            'label.max' => 'Invalid parameter "label". The length has to be at most 255.',
            'label.unique' => 'Parameter "label" has to be unique.',
        ];
    }

    protected function prepareForValidation() {
        $this->merge([
            'label' => utrim($this->label),
        ]);
    }
}
