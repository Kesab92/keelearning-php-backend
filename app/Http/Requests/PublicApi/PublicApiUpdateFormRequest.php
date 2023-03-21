<?php

namespace App\Http\Requests\PublicApi;

use Illuminate\Foundation\Http\FormRequest;

abstract class PublicApiUpdateFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return false;
    }
}
