<?php

namespace App\Models\Courses;


use App\Models\KeelearningModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Courses\CourseAccessRequest
 *
 * @property int $id
 * @property int $course_id
 * @property int $user_id
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Courses\Course $course
 * @property-read User $user
 * @method static \Illuminate\Database\Eloquent\Builder|CourseAccessRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseAccessRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseAccessRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseAccessRequest whereCourseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseAccessRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseAccessRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseAccessRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseAccessRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseAccessRequest whereUserId($value)
 * @mixin \Eloquent
 * @mixin IdeHelperCourseAccessRequest
 */
class CourseAccessRequest extends KeelearningModel
{
    const STATUS_NEW = 0;

    /**
     * @return BelongsTo
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
