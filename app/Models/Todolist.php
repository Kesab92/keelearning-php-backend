<?php

namespace App\Models;

use App\Services\MorphTypes;
use App\Traits\Duplicatable;
use App\Traits\Saferemovable;

/**
 * @mixin IdeHelperTodolist
 */
class Todolist extends KeelearningModel
{
    use Saferemovable;
    use Duplicatable;

    const TYPE_COURSE_CONTENT = MorphTypes::TYPE_COURSE_CONTENT;

    public function todolistItems() {
        return $this->hasMany(TodolistItem::class)->orderBy('position');
    }
}
