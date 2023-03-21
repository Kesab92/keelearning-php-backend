<?php

namespace App\Services;

use App\Models\User;
use URL;

class AccountActivation
{
    public function getActivationLink(User $user)
    {
        return URL::signedRoute('account-activation', ['userId' => $user->id]);
    }
}
