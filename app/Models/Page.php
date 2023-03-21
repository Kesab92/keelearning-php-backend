<?php

namespace App\Models;

use App\Traits\Saferemovable;
use App\Traits\TagRights;
use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\Page.
 *
 * @property-read App $app
 * @property int $id
 * @property int $app_id
 * @property string $title
 * @property string $content
 * @property bool $visible
 * @property bool $public
 * @property bool $show_on_auth
 * @property bool $show_in_footer
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property int|null $parent_id
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Page whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Page whereAppId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Page whereTitle($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Page whereContent($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Page whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Page whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Page whereVisible($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Page visibleToUser(\App\Models\User $user)
 * @mixin \Eloquent
 * @property int|null $parent_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PageTranslation[] $allTranslationRelations
 * @property-read int|null $all_translation_relations_count
 * @method static \Illuminate\Database\Eloquent\Builder|Page newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Page newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Page query()
 * @method static \Illuminate\Database\Eloquent\Builder|Page whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Page wherePublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Page whereShowInFooter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Page whereShowOnAuth($value)
 * @mixin IdeHelperPage
 */
class Page extends KeelearningModel
{
    use HasFactory;
    use Saferemovable;
    use TagRights;
    use Translatable;

    public $translated = ['title', 'content'];

    public function app()
    {
        return $this->belongsTo(App::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'page_tag')->withTimestamps();
    }

    /**
     * Only include pages visible to a specified user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVisibleToUser($query, User $user)
    {
        $userTags = $user->tags()->pluck('tags.id');

        return $query->where('app_id', $user->app_id)
            ->where(function ($query) use ($userTags) {
                $query->whereHas('tags', function ($query) use ($userTags) {
                    $query->whereIn('tags.id', $userTags);
                });
            })
            ->where('visible', 1);
    }

    /**
     * Only include pages visible to a specified app profile.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVisibleToAppProfile($query, AppProfile $appProfile)
    {
        $profileTags = $appProfile->tags()->pluck('tags.id');

        return $query->where('app_id', $appProfile->app_id)
            ->where(function ($query) use ($profileTags) {
                $query->whereHas('tags', function ($query) use ($profileTags) {
                    $query->whereIn('tags.id', $profileTags);
                });
            })
            ->where('visible', 1);
    }
}
