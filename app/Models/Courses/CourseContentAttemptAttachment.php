<?php

namespace App\Models\Courses;

use App\Models\KeelearningModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Courses\CourseContentAttemptAttachment
 *
 * @property int $id
 * @property int $course_content_attempt_id
 * @property int $course_content_attachment_id
 * @property string $value
 * @property int|null $passed
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Courses\CourseContentAttachment $attachment
 * @property-read \App\Models\Courses\CourseContentAttempt $attempt
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttemptAttachment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttemptAttachment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttemptAttachment query()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttemptAttachment whereCourseContentAttachmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttemptAttachment whereCourseContentAttemptId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttemptAttachment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttemptAttachment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttemptAttachment wherePassed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttemptAttachment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttemptAttachment whereValue($value)
 * @mixin IdeHelperCourseContentAttemptAttachment
 */
class CourseContentAttemptAttachment extends KeelearningModel
{
    /**
     * @return BelongsTo
     */
    public function attempt()
    {
        return $this->belongsTo(CourseContentAttempt::class, 'course_content_attempt_id');
    }

    public function attachment()
    {
        return $this->belongsTo(CourseContentAttachment::class, 'course_content_attachment_id');
    }
}
