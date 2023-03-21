<?php

namespace App\Models;

/**
 * @mixin IdeHelperAuthToken
 */
class AuthToken extends KeelearningModel
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
