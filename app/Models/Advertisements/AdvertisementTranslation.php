<?php

namespace App\Models\Advertisements;

use App\Models\KeelearningModel;

/**
 * App\Models\Advertisements\AdvertisementTranslation
 *
 * @property int $id
 * @property int $advertisement_id
 * @property string $language
 * @property string|null $description
 * @property string|null $link
 * @property string|null $rectangle_image_url
 * @property string|null $leaderboard_image_url
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Advertisements\Advertisement $advertisement
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertisementTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertisementTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertisementTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertisementTranslation whereAdvertisementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertisementTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertisementTranslation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertisementTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertisementTranslation whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertisementTranslation whereLeaderboardImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertisementTranslation whereLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertisementTranslation whereRectangleImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertisementTranslation whereUpdatedAt($value)
 * @mixin IdeHelperAdvertisementTranslation
 */
class AdvertisementTranslation extends KeelearningModel
{
    public function advertisement()
    {
        return $this->belongsTo(Advertisement::class);
    }
}
