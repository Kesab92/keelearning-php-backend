<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperUserMetafield
 */
class UserMetafield extends Model
{

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
