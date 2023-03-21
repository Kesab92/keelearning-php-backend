<?php

namespace App\Models\Courses;

use App\Models\KeelearningModel;
use App\Models\Tag;
use App\Services\MorphTypes;
use App\Traits\Duplicatable;
use App\Traits\Saferemovable;
use App\Traits\TagRights;
use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Courses\CourseContent
 *
 * @property int $id
 * @property int $course_chapter_id
 * @property int $type
 * @property int|null $foreign_id
 * @property int $position
 * @property int $visible
 * @property int $duration
 * @property int|null $pass_percentage
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $is_test
 * @property int $show_correct_result
 * @property int|null $repetitions
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Courses\CourseContentTranslation[] $allTranslationRelations
 * @property-read int|null $all_translation_relations_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Courses\CourseContentAttachment[] $attachments
 * @property-read int|null $attachments_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Courses\CourseContentAttempt[] $attempts
 * @property-read int|null $attempts_count
 * @property-read \App\Models\Courses\CourseChapter $chapter
 * @property-read \App\Models\Courses\Course|null $course
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $relatable
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContent query()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContent whereCourseChapterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContent whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContent whereForeignId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContent whereIsTest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContent wherePassPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContent wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContent whereRepetitions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContent whereShowCorrectResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContent whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContent whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContent whereVisible($value)
 * @mixin IdeHelperCourseContent
 */
class CourseContent extends KeelearningModel
{
    use Duplicatable;
    use Saferemovable;
    use Translatable;
    use TagRights;
    use HasFactory;

    public $translated = ['title', 'description'];

    const TYPE_APPOINTMENT = MorphTypes::TYPE_APPOINTMENT;
    const TYPE_CHAPTER = MorphTypes::TYPE_COURSE_CHAPTER; // Not really a course content type, but we use it in the frontend, so we define it here as well
    const TYPE_LEARNINGMATERIAL = MorphTypes::TYPE_LEARNINGMATERIAL;
    const TYPE_FORM = MorphTypes::TYPE_FORM;
    const TYPE_QUESTIONS = MorphTypes::TYPE_COURSE_CONTENT_QUESTIONS;
    const TYPE_CERTIFICATE = MorphTypes::TYPE_CERTIFICATE;
    const TYPE_TODOLIST = MorphTypes::TYPE_TODOLIST;

    const TYPES = [
        self::TYPE_APPOINTMENT,
        self::TYPE_CERTIFICATE,
        self::TYPE_CHAPTER,
        self::TYPE_LEARNINGMATERIAL,
        self::TYPE_FORM,
        self::TYPE_QUESTIONS,
        self::TYPE_TODOLIST,
    ];

    /**
     * @return BelongsTo
     */
    public function chapter()
    {
        return $this->belongsTo(CourseChapter::class, 'course_chapter_id');
    }

    public function attachments()
    {
        return $this->hasMany(CourseContentAttachment::class);
    }

    public function attempts()
    {
        return $this->hasMany(CourseContentAttempt::class);
    }

    public function course()
    {
        return $this->hasOneThrough(Course::class, CourseChapter::class, 'id', 'id', 'course_chapter_id', 'course_id');
    }

    /**
     * Get the owning commentable model.
     */
    public function relatable()
    {
        return $this->morphTo(null, 'type', 'foreign_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    /**
     * Returns true for content types which can have a foreign object, for example learning material contents.
     */
    public function canHaveForeignObject()
    {
        return in_array($this->type, [
            self::TYPE_LEARNINGMATERIAL,
            self::TYPE_CERTIFICATE,
            self::TYPE_FORM,
        ]);
    }

    /**
     * Returns true for content types which need an attached foreign object to be displayed, for example certificates.
     */
    public function needsForeignObject()
    {
        return in_array($this->type, [
            self::TYPE_CERTIFICATE,
            self::TYPE_TODOLIST,
        ]);
    }

    public function isRepeatable()
    {
        if ($this->isEndlesslyRepeatable()) {
            return true;
        }

        return $this->repetitions > 1;
    }

    public function isEndlesslyRepeatable()
    {
        return $this->repetitions === null;
    }
}
