<?php

namespace App\Models;

use App\Traits\Duplicatable;
use App\Traits\Saferemovable;
use App\Traits\Translatable;

/**
 * @mixin IdeHelperTodolistItem
 */
class TodolistItem extends KeelearningModel
{
    use Translatable;
    use Saferemovable;
    use Duplicatable;

    public $translated = ['title', 'description'];

    public function todolist() {
        return $this->belongsTo(Todolist::class);
    }

    public function answers() {
        return $this->hasMany(TodolistItemAnswer::class);
    }
}
