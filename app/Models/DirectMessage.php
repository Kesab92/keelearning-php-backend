<?php

namespace App\Models;

/**
 * @mixin IdeHelperDirectMessage
 */
class DirectMessage extends KeelearningModel
{

    protected $dates = [
        'created_at',
        'updated_at',
        'read_at',
    ];

    public function app()
    {
        return $this->belongsTo(App::class);
    }

    public function recipient()
    {
        return $this->belongsTo(User::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class);
    }
}
