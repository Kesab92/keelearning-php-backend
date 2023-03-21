<?php

namespace App\Http\Requests\PublicApi\User;

use App\Http\Requests\PublicApi\PublicApiDeleteFormRequest;
use App\Models\User;
use Auth;

class UserDeleteFormRequest extends PublicApiDeleteFormRequest {

    public function authorize()
    {
        $user = User
            ::where('is_dummy', false)
            ->where('is_api_user', false)
            ->find($this->resourceId);

        if(!$user || $user->app_id !== Auth::user()->app_id) {
            return false;
        }

        return true;
    }
}
