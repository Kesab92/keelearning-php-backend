<?php

namespace App\Models\Forms;

use App\Models\KeelearningModel;

/**
 * @mixin IdeHelperFormAnswerField
 */
class FormAnswerField extends KeelearningModel
{

    public function formAnswer()
    {
        return $this->belongsTo(FormAnswer::class);
    }

    public function formField()
    {
        return $this->belongsTo(FormField::class);
    }

    function getAnswerAttribute($value)
    {
        return $this->formField->type === FormField::TYPE_RATING ? (int) $value : $value;
    }

    public function getFormattedAnswer() {

        switch ($this->formField->type) {
            case FormField::TYPE_RATING:
                return $this->answer === 1 ? '1 Stern' : $this->answer.' Sterne';
            default:
            return $this->answer;
        }
    }
}
