<?php

namespace App\Http\Requests\BackendApi\UserRole;

use App\Models\UserRole;
use App\Models\UserRoleRight;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRoleUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->isMainAdmin() && UserRole::where('app_id', appId())->find($this->id);
    }

    public function rules()
    {
        return [
            'name' => [
                'sometimes',
                'string',
                'between:3,255',
                Rule::unique('user_roles')->where(function ($query) {
                    $query->where('app_id', appId());
                })->ignore($this->id),
            ],
            'rules' => [
                'sometimes',
                'array',
            ],
            'rules.*' => [
                'string',
                Rule::in(UserRoleRight::RIGHT_TYPES),
            ],
        ];
    }

    protected function prepareForValidation() {
        $this->merge([
            'name' => utrim($this->name),
        ]);
    }
}
