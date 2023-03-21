<?php

namespace App\Models;

use App\Traits\Duplicatable;

/**
 * @mixin IdeHelperCertificateTemplateTranslation
 */
class CertificateTemplateTranslation extends KeelearningModel
{
    use Duplicatable;

    public function certificateTemplate()
    {
        return $this->belongsTo(CertificateTemplate::class);
    }
}
