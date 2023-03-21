<?php

namespace App\Models;

/**
 * @mixin IdeHelperTodolistItemAnswer
 */
class TodolistItemAnswer extends KeelearningModel
{
    public function todolistItem() {
        return $this->belongsTo(TodolistItem::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
