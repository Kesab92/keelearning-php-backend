<?php

namespace App\Models;

/**
 * App\Models\Categorygroup.
 *
 * @property-read \App\Models\App $app
 * @property int $id
 * @property int $app_id
 * @property string $name
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Category[] $categories
 * @method static \Illuminate\Database\Query\Builder|\App\Models\QuizTeam whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\QuizTeam whereAppId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\QuizTeam whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\QuizTeam whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\QuizTeam whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\QuizTeam whereOwnerId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereTagIds($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CategorygroupTranslation[] $allTranslationRelations
 * @property-read int|null $all_translation_relations_count
 * @property-read int|null $categories_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @property-read int|null $tags_count
 * @method static \Illuminate\Database\Eloquent\Builder|Categorygroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Categorygroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Categorygroup ofApp($appId)
 * @method static \Illuminate\Database\Eloquent\Builder|Categorygroup query()
 * @mixin IdeHelperCategorygroup
 */
class Categorygroup extends KeelearningModel
{
    use \App\Traits\Duplicatable;
    use \App\Traits\Saferemovable;
    use \App\Traits\Translatable;

    public $translated = ['name'];

    public function app()
    {
        return $this->belongsTo(\App\Models\App::class);
    }

    public function categories()
    {
        return $this->hasMany(\App\Models\Category::class);
    }

    public function tags()
    {
        return $this->belongsToMany(\App\Models\Tag::class)->withTimestamps();
    }

    /**
     * Limits the query to the scope of groups of the app with the given id.
     *
     * @param $query
     * @param $appId
     *
     * @return mixed
     */
    public function scopeOfApp($query, $appId)
    {
        return $query->where('app_id', '=', $appId);
    }
}
