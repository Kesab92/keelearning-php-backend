<?php

namespace App\Models\Forms;

use App\Models\KeelearningModel;
use App\Models\User;
use App\Traits\Saferemovable;

/**
 * @mixin IdeHelperFormAnswer
 */
class FormAnswer extends KeelearningModel
{
    use Saferemovable;

    public function form()
    {
        return $this->belongsTo(Form::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function fields()
    {
        return $this->hasMany(FormAnswerField::class);
    }
    public function relatable()
    {
        return $this->morphTo(null, 'foreign_type', 'foreign_id');
    }
}
