<?php

namespace App\Models;

use App\Traits\Duplicatable;

/**
 * @mixin IdeHelperAzureVideoSubtitle
 */
class AzureVideoSubtitle extends KeelearningModel
{
    use Duplicatable;

    public function azureVideo()
    {
        return $this->belongsTo(AzureVideo::class);
    }
}
