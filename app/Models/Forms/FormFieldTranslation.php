<?php

namespace App\Models\Forms;

use App\Models\KeelearningModel;
use App\Traits\Duplicatable;

/**
 * @mixin IdeHelperFormFieldTranslation
 */
class FormFieldTranslation extends KeelearningModel
{
    use Duplicatable;
    public function field()
    {
        return $this->belongsTo(FormField::class);
    }
}
