<?php

namespace App\Models;

/**
 * @mixin IdeHelperOpenIdToken
 */
class OpenIdToken extends KeelearningModel
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
