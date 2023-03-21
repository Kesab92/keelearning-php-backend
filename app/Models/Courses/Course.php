<?php

namespace App\Models\Courses;

use App\Models\App;
use App\Models\Comments\Comment;
use App\Models\ContentCategories\ContentCategory;
use App\Models\KeelearningModel;
use App\Models\Reminder;
use App\Models\Tag;
use App\Models\User;
use App\Services\MorphTypes;
use App\Traits\Duplicatable;
use App\Traits\Saferemovable;
use App\Traits\TagRights;
use App\Traits\Translatable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

/**
 * App\Models\Courses\Course
 *
 * @property int $id
 * @property int $app_id
 * @property \Illuminate\Support\Carbon|null $available_from
 * @property \Illuminate\Support\Carbon|null $available_until
 * @property int $visible
 * @property int $duration_type
 * @property int $participation_duration
 * @property int $participation_duration_type
 * @property string|null $cover_image_url
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $preview_enabled
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Courses\CourseTranslation[] $allTranslationRelations
 * @property-read int|null $all_translation_relations_count
 * @property-read App $app
 * @property-read \Illuminate\Database\Eloquent\Collection|ContentCategory[] $categories
 * @property-read int|null $categories_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Courses\CourseChapter[] $chapters
 * @property-read int|null $chapters_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Courses\CourseContent[] $contents
 * @property-read int|null $contents_count
 * @property-read \Illuminate\Database\Eloquent\Collection|User[] $managers
 * @property-read int|null $managers_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Courses\CourseParticipation[] $participations
 * @property-read int|null $participations_count
 * @property-read \Illuminate\Database\Eloquent\Collection|Tag[] $previewTags
 * @property-read int|null $preview_tags_count
 * @property-read \Illuminate\Database\Eloquent\Collection|Tag[] $tags
 * @property-read int|null $tags_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Courses\CourseContent[] $visibleContents
 * @property-read int|null $visible_contents_count
 * @method static \Illuminate\Database\Eloquent\Builder|Course newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Course newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Course query()
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereAvailableFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereAvailableUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereCoverImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course wherePreviewEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereVisible($value)
 * @mixin IdeHelperCourse
 */
class Course extends KeelearningModel
{
    use Duplicatable;
    use HasFactory;
    use Saferemovable;
    use Translatable;
    use TagRights;

    const INTERVAL_WEEKLY = 0;
    const INTERVAL_MONTHLY = 1;

    const DURATION_TYPE_FIXED = 0;
    const DURATION_TYPE_DYNAMIC = 1;

    const PARTICIPATION_DURATION_DAYS = 0;
    const PARTICIPATION_DURATION_WEEKS = 1;
    const PARTICIPATION_DURATION_MONTHS = 2;


    protected $dates = [
        'created_at',
        'updated_at',
        'available_from',
        'available_until',
    ];

    public $translated = [
        'title',
        'description',
        'request_access_link',
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('not_template', function ($query) {
            $query->where('is_template', 0);
        });
    }

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

    public function previewTags()
    {
        return $this->belongsToMany(Tag::class, 'course_preview_tag')->withTimestamps();
    }

    public function chapters()
    {
        return $this->hasMany(CourseChapter::class)->orderBy('position');
    }

    public function contents()
    {
        return $this->hasManyThrough(CourseContent::class, CourseChapter::class);
    }

    public function visibleContents()
    {
        return $this->hasManyThrough(CourseContent::class, CourseChapter::class)
            ->where('course_contents.visible', 1);
    }

    public function participations()
    {
        return $this->hasMany(CourseParticipation::class);
    }

    public function categories()
    {
        return $this->belongsToMany(ContentCategory::class, 'content_category_relations', 'foreign_id', 'content_category_id')
            ->where('content_category_relations.type', ContentCategory::TYPE_COURSES)
            ->withTimestamps();
    }

    public function managers()
    {
        return $this->belongsToMany(User::class, 'course_managers')->withTimestamps();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'foreign');
    }

    public function reminders()
    {
        return $this->hasMany(Reminder::class, 'foreign_id')
            ->whereIn('type', Reminder::TYPES[MorphTypes::TYPE_COURSE]);
    }

    public function earliestReminder()
    {
        return $this->hasOne(Reminder::class, 'foreign_id')
            ->whereIn('type', Reminder::TYPES[MorphTypes::TYPE_COURSE])
            ->ofMany([
                'days_offset' => 'max',
            ]);
    }

    public function awardTags()
    {
        return $this->belongsToMany(Tag::class, 'course_award_tags')->withTimestamps();
    }

    public function retractTags()
    {
        return $this->belongsToMany(Tag::class, 'course_retract_tags')->withTimestamps();
    }

    public function parent()
    {
        return $this->belongsTo(Course::class, 'parent_course_id')->template();
    }

    public function latestRepeatedCourse()
    {
        return $this->hasOne(Course::class, 'parent_course_id')->ofMany([
            'created_at' => 'max',
        ]);
    }

    public function templateInheritanceApps()
    {
        return $this->belongsToMany(App::class, 'course_template_inheritances', 'course_id', 'app_id')->withTimestamps();
    }

    public function individualAttendees()
    {
        return $this->belongsToMany(User::class, 'course_individual_attendees', 'course_id', 'user_id')->withTimestamps();
    }

    public function scopeOfApp($query, $appId)
    {
        return $query->where('app_id', $appId);
    }

    public function scopeTemplate($query)
    {
        return $query->withoutGlobalScope('not_template')->where('is_template', 1);
    }

    public function scopeWithTemplates($query)
    {
        return $query->withoutGlobalScope('not_template');
    }

    public function scopeRepeatingTemplate($query)
    {
        return $query->template()
            ->where('visible', 1)
            ->where('is_repeating', 1)
            ->whereNotNull('available_from')
            ->whereNotNull('repetition_interval');
    }

    public function scopeMandatory(Builder $query): Builder
    {
        return $query->where('is_mandatory', 1);
    }

    public function scopeCurrentAndPast(Builder $query): Builder
    {
        return $query->where('visible', 1)
            ->whereNull('archived_at')
            ->where(function (Builder $subQuery) {
                $subQuery
                    ->whereNull('available_from')
                    ->orWhere('available_from', '<=', Carbon::now());
            });
    }

    /**
     * Check if the user can preview the course, but not take it
     *
     * @param User|null $user
     * @return bool
     */
    public function isPreview(User $user): bool
    {
        if(!$this->preview_enabled) {
            return false;
        }
        // Admins have access to all courses
        if($user->is_admin) {
            return false;
        }
        $userTagIDs = $user->tags->pluck('id');
        $tagIDs = $this->tags->pluck('id');

        $hasTagAccess = $tagIDs->isEmpty() || $tagIDs->intersect($userTagIDs)->isNotEmpty();
        $hasIndividualAccess = $this->individualAttendees->contains($user->id);

        $hasRegularAccess = ($this->has_individual_attendees && $hasIndividualAccess) || (!$this->has_individual_attendees && $hasTagAccess);
        if($hasRegularAccess) {
            return false;
        }

        $previewTagIDs = $this->previewTags->pluck('id');
        $commonPreviewTags = $previewTagIDs->intersect($userTagIDs);
        $hasPreviewAccess = $previewTagIDs->isEmpty() || $commonPreviewTags->isNotEmpty();
        if ($hasPreviewAccess) {
            return true;
        }

        return false;
    }

    /**
     * Returns the next repetition date.
     *
     * @return Carbon|null
     */
    public function getNextRepetitionDateAttribute() {
        return $this->getNextRepetitionDate();
    }

    /**
     * Returns the course path in the frontend app.
     * @return string
     */
    public function getCoursePath() {
        return '/courses/' . $this->id;
    }

    public function getFrontendUrl(?User $user = null)
    {
        if ($user) {
            if ($user->app_id == $this->app_id) {
                return $user->getAppProfile()->app_hosted_at . '/courses/' . $this->id;
            }
        }
        return $this->app->getDefaultAppProfile()->app_hosted_at . '/courses/' . $this->id;
    }

    public function getTrackWbtsAttribute()
    {
        return $this->created_at->isAfter(Carbon::create(env('WBT_TRACKING_THRESHOLD', '2021-08-31') . ' 0:00'));
    }

    public function getAvailableStatusAttribute()
    {
        if (!$this->getOriginal('visible')) {
            return false;
        }
        if ($this->available_from && $this->available_from->isFuture()) {
            return false;
        }
        if (
            $this->duration_type == self::DURATION_TYPE_FIXED
            && $this->available_until
            && $this->available_until->isPast()
        ) {
            return false;
        }

        return true;
    }

    public function getNextRepetitionDate($ignoreCommandRunningHour = false) {
        if(!$this->getOriginal('visible') || !$this->is_repeating || !$this->available_from || !$this->repetition_interval) {
            return null;
        }

        if(!in_array($this->repetition_interval_type, [Course::INTERVAL_WEEKLY, Course::INTERVAL_MONTHLY])) {
            return null;
        }

        $repetitionDate = $this->available_from;

        if($this->latestRepeatedCourse && $this->latestRepeatedCourse->created_at->gt($this->available_from)) {
            $repetitionDate = $this->latestRepeatedCourse->created_at;

            switch($this->repetition_interval_type) {
                case Course::INTERVAL_WEEKLY:
                    $repetitionDate->addWeeks($this->repetition_interval);
                    break;
                case Course::INTERVAL_MONTHLY:
                    $repetitionDate->addMonths($this->repetition_interval);
                    break;
            }
        }

        if ($repetitionDate->isPast()) {
            $repetitionDate = Carbon::today();
            // new courses are created at 6am
            if (!$ignoreCommandRunningHour && Carbon::now()->hour >= 6) {
                $repetitionDate->addDay();
            }
        }

        return $repetitionDate;
    }

    public function getCourseMaxDuration()
    {
        return $this->contents->where('visible', 1)->sum(function ($content) {
            return $content->duration;
        });
    }
}
