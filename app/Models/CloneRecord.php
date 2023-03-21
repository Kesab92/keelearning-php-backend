<?php

namespace App\Models;

/**
 * @mixin IdeHelperCloneRecord
 */
class CloneRecord extends KeelearningModel
{
    protected $fillable = [
        'source_id',
        'target_app_id',
        'target_id',
        'type',
    ];
}
