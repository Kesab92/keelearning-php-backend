<?php

namespace App\Models\Advertisements;

use App\Models\App;
use App\Models\KeelearningModel;
use App\Models\Tag;
use App\Models\User;
use App\Traits\Saferemovable;
use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Advertisements\Advertisement
 *
 * @property int $id
 * @property int $app_id
 * @property string $name
 * @property int $visible
 * @property int $is_ad
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Advertisements\AdvertisementTranslation[] $allTranslationRelations
 * @property-read int|null $all_translation_relations_count
 * @property-read App $app
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Advertisements\AdvertisementPosition[] $positions
 * @property-read int|null $positions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|Tag[] $tags
 * @property-read int|null $tags_count
 * @method static Builder|Advertisement newModelQuery()
 * @method static Builder|Advertisement newQuery()
 * @method static Builder|Advertisement public($app_id)
 * @method static Builder|Advertisement query()
 * @method static Builder|Advertisement visibleToUser(\App\Models\User $user)
 * @method static Builder|Advertisement whereAppId($value)
 * @method static Builder|Advertisement whereCreatedAt($value)
 * @method static Builder|Advertisement whereId($value)
 * @method static Builder|Advertisement whereIsAd($value)
 * @method static Builder|Advertisement whereName($value)
 * @method static Builder|Advertisement whereUpdatedAt($value)
 * @method static Builder|Advertisement whereVisible($value)
 * @mixin IdeHelperAdvertisement
 */
class Advertisement extends KeelearningModel
{
    use Saferemovable;
    use Translatable;

    public $translated = ['description', 'link', 'rectangle_image_url', 'leaderboard_image_url'];

    const POSITIONS = [
        'POSITION_LOGIN' => 0,
        'POSITION_HOME_MIDDLE' => 1,
        'POSITION_HOME_BOTTOM' => 8,
        'POSITION_NEWS' => 2,
        'POSITION_MEDIALIBRARY' => 3,
        'POSITION_POWERLEARNING' => 4,
        'POSITION_INDEXCARDS' => 5,
        'POSITION_QUIZ' => 6,
        'POSITION_TESTS' => 7,
    ];

    /**
     * @return BelongsTo
     */
    public function app()
    {
        return $this->belongsTo(App::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function positions()
    {
        return $this->hasMany(AdvertisementPosition::class);
    }

    /**
     * Only include advertisements visible to a specified user.
     *
     * @param Builder $query
     * @param User $user
     * @return Builder
     */
    public function scopeVisibleToUser($query, User $user)
    {
        $userTags = $user->tags()->pluck('tags.id');

        return $query
            ->where('visible', 1)
            ->where('app_id', $user->app_id)
            ->where(function ($query) use ($userTags) {
                $query->doesntHave('tags')
                    ->orWhereHas('tags', function ($query) use ($userTags) {
                        $query->whereIn('tags.id', $userTags);
                    });
            });
    }

    /**
     * Only include advertisements which are public for a given app
     *
     * @param Builder $query
     * @param $app_id
     * @return Builder
     */
    public function scopePublic($query, $app_id)
    {
        return $query
            ->where('visible', 1)
            ->where('app_id', $app_id)
            ->where(function ($query) {
                $query->doesntHave('tags');
            });
    }
}
