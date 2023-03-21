<?php

namespace App\Models\Advertisements;

use App\Models\KeelearningModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Advertisements\AdvertisementPosition
 *
 * @property int $id
 * @property int $advertisement_id
 * @property int $position
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Advertisements\Advertisement $advertisement
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertisementPosition newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertisementPosition newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertisementPosition query()
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertisementPosition whereAdvertisementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertisementPosition whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertisementPosition whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertisementPosition wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertisementPosition whereUpdatedAt($value)
 * @mixin IdeHelperAdvertisementPosition
 */
class AdvertisementPosition extends KeelearningModel
{
    /**
     * @return BelongsTo
     */
    public function advertisement()
    {
        return $this->belongsTo(Advertisement::class);
    }
}
