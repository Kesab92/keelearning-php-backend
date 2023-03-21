<?php

namespace App\Models\Courses;

use App\Models\KeelearningModel;
use App\Traits\Duplicatable;

/**
 * App\Models\Courses\CourseChapterTranslation
 *
 * @property int $id
 * @property int $course_chapter_id
 * @property string $language
 * @property string $title
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Courses\CourseChapter $chapter
 * @method static \Illuminate\Database\Eloquent\Builder|CourseChapterTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseChapterTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseChapterTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseChapterTranslation whereCourseChapterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseChapterTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseChapterTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseChapterTranslation whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseChapterTranslation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseChapterTranslation whereUpdatedAt($value)
 * @mixin IdeHelperCourseChapterTranslation
 */
class CourseChapterTranslation extends KeelearningModel
{
    use Duplicatable;

    public function chapter()
    {
        return $this->belongsTo(CourseChapter::class, 'course_chapter_id');
    }
}
