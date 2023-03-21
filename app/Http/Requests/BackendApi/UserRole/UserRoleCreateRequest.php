<?php

namespace App\Http\Requests\BackendApi\UserRole;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRoleCreateRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'between:3,255',
                Rule::unique('user_roles')->where(function ($query) {
                    $query->where('app_id', appId());
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
