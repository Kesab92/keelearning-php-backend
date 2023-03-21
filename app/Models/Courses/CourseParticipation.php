<?php

namespace App\Models\Courses;

use App\Models\KeelearningModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Courses\CourseParticipation
 *
 * @property int $id
 * @property int $course_id
 * @property int $user_id
 * @property int|null $passed
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $finished_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Courses\CourseContentAttempt[] $contentAttempts
 * @property-read int|null $content_attempts_count
 * @property-read \App\Models\Courses\Course $course
 * @property-read User $user
 * @method static \Illuminate\Database\Eloquent\Builder|CourseParticipation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseParticipation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseParticipation query()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseParticipation whereCourseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseParticipation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseParticipation whereFinishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseParticipation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseParticipation wherePassed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseParticipation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseParticipation whereUserId($value)
 * @mixin IdeHelperCourseParticipation
 */
class CourseParticipation extends KeelearningModel
{
    use HasFactory;

    protected $dates = [
        'finished_at',
    ];

    /**
     * @return BelongsTo
     */
    public function course()
    {
        return $this->belongsTo(Course::class)->withTemplates();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function contentAttempts()
    {
        return $this->hasMany(CourseContentAttempt::class);
    }

    /**
     * Returns the end date of the participation,
     * either because the course ends or the participation expires.
     *
     * @return \Illuminate\Support\Carbon|null
     */
    public function availableUntil()
    {
        if ($this->course->duration_type == Course::DURATION_TYPE_FIXED) {
            return $this->course->available_until;
        }
        if ($this->course->duration_type == Course::DURATION_TYPE_DYNAMIC) {
            $availableUntil = $this->created_at->clone();
            switch ($this->course->participation_duration_type) {
                case Course::PARTICIPATION_DURATION_DAYS:
                    $availableUntil->addDays($this->course->participation_duration);
                    break;
                case Course::PARTICIPATION_DURATION_WEEKS:
                    $availableUntil->addWeeks($this->course->participation_duration);
                    break;
                case Course::PARTICIPATION_DURATION_MONTHS:
                    $availableUntil->addMonths($this->course->participation_duration);
                    break;
            }
            return $availableUntil;
        }
        return null;
    }
}
