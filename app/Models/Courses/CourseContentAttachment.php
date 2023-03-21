<?php

namespace App\Models\Courses;

use App\Models\KeelearningModel;
use App\Services\MorphTypes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Courses\CourseContentAttachment
 *
 * @property int $id
 * @property int $course_content_id
 * @property int $position
 * @property int $type
 * @property int $foreign_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $attachment
 * @property-read \App\Models\Courses\CourseContent $content
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttachment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttachment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttachment query()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttachment whereCourseContentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttachment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttachment whereForeignId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttachment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttachment wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttachment whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttachment whereUpdatedAt($value)
 * @mixin IdeHelperCourseContentAttachment
 */
class CourseContentAttachment extends KeelearningModel
{
    use \App\Traits\Duplicatable;

    const TYPE_QUESTION = MorphTypes::TYPE_QUESTION;

    /**
     * @return BelongsTo
     */
    public function content()
    {
        return $this->belongsTo(CourseContent::class, 'course_content_id');
    }

    /**
     * Get the owning model.
     */
    public function attachment()
    {
        return $this
            ->morphTo('attachment', 'type', 'foreign_id');
    }
}
