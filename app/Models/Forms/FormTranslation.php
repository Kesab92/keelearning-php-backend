<?php

namespace App\Models\Forms;

use App\Models\KeelearningModel;
use App\Traits\Duplicatable;

/**
 * @mixin IdeHelperFormTranslation
 */
class FormTranslation extends KeelearningModel
{
    use Duplicatable;
    public function form()
    {
        return $this->belongsTo(Form::class);
    }
}
