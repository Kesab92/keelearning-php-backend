<?php

namespace App\Models\Courses;

use App\Models\KeelearningModel;
use App\Traits\Duplicatable;

/**
 * App\Models\Courses\CourseTranslation
 *
 * @property int $id
 * @property string $language
 * @property int $course_id
 * @property string $title
 * @property string $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Courses\Course $course
 * @method static \Illuminate\Database\Eloquent\Builder|CourseTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseTranslation whereCourseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseTranslation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseTranslation whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseTranslation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseTranslation whereUpdatedAt($value)
 * @mixin IdeHelperCourseTranslation
 */
class CourseTranslation extends KeelearningModel
{
    use Duplicatable;

    public function course()
    {
        return $this->belongsTo(Course::class)->withTemplates();
    }
}
