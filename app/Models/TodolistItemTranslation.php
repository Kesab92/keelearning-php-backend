<?php

namespace App\Models;

use App\Traits\Duplicatable;
use App\Traits\Translatable;

/**
 * @mixin IdeHelperTodolistItemTranslation
 */
class TodolistItemTranslation extends KeelearningModel
{
    use Duplicatable;
    
    public function todolistItem() {
        return $this->belongsTo(TodolistItem::class);
    }
}
