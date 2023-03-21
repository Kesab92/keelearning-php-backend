<?php

namespace App\Models;

use App\Models\Advertisements\Advertisement;
use App\Models\ContentCategories\ContentCategory;
use App\Models\Courses\Course;
use App\Traits\Saferemovable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Tag.
 *
 * @property int $id
 * @property string $label
 * @property int $creator_id
 * @property int $tag_group_id
 * @property bool $exclusive
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $deleted_at
 * @property-read \App\Models\User $creator
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Tag whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Tag whereLabel($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Tag whereCreatorId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Tag whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Tag whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Tag whereDeletedAt($value)
 * @mixin \Eloquent
 * @property int $app_id
 * @property-read \App\Models\App $app
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Tag whereAppId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Category[] $categories
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Competition[] $competitions
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Courses\Course[] courses
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Tag whereExclusive($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Tag ofApp($appId)
 * @property int $hideHighscore
 * @property-read int|null $categories_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Categorygroup[] $categorygroups
 * @property-read int|null $categorygroups_count
 * @property-read int|null $competitions_count
 * @property-read int|null $courses_count
 * @property-read \App\Models\TagGroup|null $tagGroup
 * @property-read int|null $users_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Webinar[] $webinars
 * @property-read int|null $webinars_count
 * @method static \Illuminate\Database\Eloquent\Builder|Tag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tag newQuery()
 * @method static \Illuminate\Database\Query\Builder|Tag onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Tag query()
 * @method static \Illuminate\Database\Eloquent\Builder|Tag rights(\App\Models\User $user)
 * @method static \Illuminate\Database\Eloquent\Builder|Tag whereHideHighscore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tag whereTagGroupId($value)
 * @method static \Illuminate\Database\Query\Builder|Tag withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Tag withoutTrashed()
 * @mixin IdeHelperTag
 */
class Tag extends KeelearningModel
{
    use HasFactory;
    use Saferemovable;
    use SoftDeletes;

    /**
     * Relations.
     */
    public function creator()
    {
        return $this->hasOne(\App\Models\User::class, 'id', 'creator_id');
    }

    public function app()
    {
        return $this->belongsTo(\App\Models\App::class, 'id', 'app_id');
    }

    public function analyticsEvents()
    {
        return $this->belongsToMany(AnalyticsEvent::class);
    }

    public function users()
    {
        return $this->belongsToMany(\App\Models\User::class)->withTimestamps();
    }

    public function webinars()
    {
        return $this->belongsToMany(\App\Models\Webinar::class, 'webinar_tags')->withTimestamps();
    }

    public function categories()
    {
        return $this->belongsToMany(\App\Models\Category::class)->withTimestamps();
    }

    public function categorygroups()
    {
        return $this->belongsToMany(\App\Models\Categorygroup::class)->withTimestamps();
    }

    public function competitions()
    {
        return $this->belongsToMany(\App\Models\Competition::class)->withTimestamps();
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class)->withTimestamps();
    }

    public function tests()
    {
        return $this->belongsToMany(Test::class, 'test_tags')->withTimestamps();
    }

    public function news()
    {
        return $this->belongsToMany(News::class)->withTimestamps();
    }

    public function learningmaterials()
    {
        return $this->belongsToMany(LearningMaterial::class, 'learning_material_tags')->withTimestamps();
    }

    public function learningmaterialfolders()
    {
        return $this->belongsToMany(LearningMaterialFolder::class, 'learning_material_folder_tags')->withTimestamps();
    }

    public function vouchers()
    {
        return $this->belongsToMany(Voucher::class, 'voucher_tags')->withTimestamps();
    }

    public function advertisements()
    {
        return $this->belongsToMany(Advertisement::class)->withTimestamps();
    }

    public function pages()
    {
        return $this->belongsToMany(Page::class)->withTimestamps();
    }

    public function contentcategories()
    {
        return $this->belongsToMany(ContentCategory::class, 'content_category_relations', 'foreign_id', 'content_category_id')
            ->where('content_category_relations.type', ContentCategory::TYPE_TAGS)
            ->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tagGroup()
    {
        return $this->belongsTo(TagGroup::class);
    }

    /**
     * Limits the query to the scope of tags of the app with the given id.
     *
     * @param $query
     * @param $appId
     *
     * @return mixed
     */
    public function scopeOfApp($query, $appId)
    {
        return $query->where('tags.app_id', '=', $appId);
    }

    /**
     * Returns only tags which is a user allowed to change.
     * @param $query
     * @param User $user
     * @return mixed
     */
    public function scopeRights($query, User $user)
    {
        if ($user->tagRightsRelation->count() == 0) {
            return $query;
        }

        return $query->whereIn('id', $user->tagRightsRelation->pluck('id'));
    }
}
