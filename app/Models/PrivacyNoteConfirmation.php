<?php

namespace App\Models;

/**
 * @mixin IdeHelperPrivacyNoteConfirmation
 */
class PrivacyNoteConfirmation extends KeelearningModel
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
