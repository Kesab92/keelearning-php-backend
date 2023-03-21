<?php

namespace App\Http\Requests\PublicApi;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

abstract class PublicApiDeleteFormRequest extends FormRequest
{
    public function all($keys = null) {
        $data = parent::all($keys);
        $routeParameters = $this->route()->parameters();
        $input = array_merge($data, $routeParameters);

        if($keys === null) {
            return $input;
        }
        $data = [];
        foreach (is_array($keys) ? $keys : func_get_args() as $key) {
            Arr::set($data, $key, Arr::get($input, $key));
        }
        return $data;
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            'resourceId' => 'required|integer|min:1',
        ];
    }

    public function messages() {
        return [
            'resourceId.required' => 'Parameter "resourceId" is required.',
            'resourceId.integer' => 'Invalid parameter "resourceId". It should be an integer.',
            'resourceId.min' => 'Invalid parameter "resourceId". It has to be at least 1.',
        ];
    }
}
