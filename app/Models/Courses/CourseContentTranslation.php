<?php

namespace App\Models\Courses;

use App\Models\KeelearningModel;
use App\Traits\Duplicatable;

/**
 * App\Models\Courses\CourseContentTranslation
 *
 * @property int $id
 * @property int $course_content_id
 * @property string $language
 * @property string $title
 * @property string $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Courses\CourseContent $content
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentTranslation whereCourseContentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentTranslation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentTranslation whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentTranslation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentTranslation whereUpdatedAt($value)
 * @mixin IdeHelperCourseContentTranslation
 */
class CourseContentTranslation extends KeelearningModel
{
    use Duplicatable;

    public function courseContent()
    {
        return $this->belongsTo(CourseContent::class);
    }
}
