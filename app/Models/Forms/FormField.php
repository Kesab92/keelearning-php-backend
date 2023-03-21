<?php

namespace App\Models\Forms;

use App\Models\KeelearningModel;
use App\Traits\Duplicatable;
use App\Traits\Saferemovable;
use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @mixin IdeHelperFormField
 */
class FormField extends KeelearningModel
{
    use Duplicatable;
    use HasFactory;
    use Translatable;
    use Saferemovable;

    public $translated = [
        'title',
    ];

    const TYPE_TEXTAREA = 1;
    const TYPE_RATING = 2;
    const TYPE_HEADER = 3;
    const TYPE_SEPARATOR = 4;

    const ALL_TYPES = [
        self::TYPE_TEXTAREA,
        self::TYPE_RATING,
        self::TYPE_HEADER,
        self::TYPE_SEPARATOR,
    ];

    const READONLY_TYPES = [
        self::TYPE_HEADER,
        self::TYPE_SEPARATOR,
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function answers()
    {
        return $this->hasMany(FormAnswerField::class);
    }

    public function getFormattedTitle() {
        switch ($this->type) {
            case FormField::TYPE_RATING:
                return $this->title ?: 'Sternebewertung';
            default:
                return $this->title;
        }
    }
}
