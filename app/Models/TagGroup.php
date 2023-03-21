<?php

namespace App\Models;

/**
 * App\Models\TagGroup
 *
 * @property int $id
 * @property int $app_id
 * @property string $name
 * @property int $can_have_duplicates
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $signup_selectable
 * @property int $show_highscore_tag
 * @property int $signup_required
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @property-read int|null $tags_count
 * @method static \Illuminate\Database\Eloquent\Builder|TagGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TagGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TagGroup ofApp($appId)
 * @method static \Illuminate\Database\Eloquent\Builder|TagGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder|TagGroup whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagGroup whereCanHaveDuplicates($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagGroup whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagGroup whereShowHighscoreTag($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagGroup whereSignupRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagGroup whereSignupSelectable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagGroup whereUpdatedAt($value)
 * @mixin IdeHelperTagGroup
 */
class TagGroup extends KeelearningModel
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tags()
    {
        return $this->hasMany(Tag::class);
    }

    /**
     * Limits the query to the scope of taggroups of the app with the given id.
     *
     * @param $query
     * @param $appId
     *
     * @return mixed
     */
    public function scopeOfApp($query, $appId)
    {
        return $query->where('tag_groups.app_id', '=', $appId);
    }
}
