<?php

namespace App\Services\Access;

use App\Models\User;

interface AccessInterface
{
    public function hasAccess(User $user, $resource);
}
