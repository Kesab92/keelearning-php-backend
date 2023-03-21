<?php

namespace App\Models\Courses;


use App\Traits\Saferemovable;
use App\Models\KeelearningModel;
use App\Traits\Duplicatable;
use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Courses\CourseChapter
 *
 * @property int $id
 * @property int $course_id
 * @property int $position
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Courses\CourseChapterTranslation[] $allTranslationRelations
 * @property-read int|null $all_translation_relations_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Courses\CourseContent[] $contents
 * @property-read int|null $contents_count
 * @property-read \App\Models\Courses\Course $course
 * @method static \Illuminate\Database\Eloquent\Builder|CourseChapter newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseChapter newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseChapter query()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseChapter whereCourseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseChapter whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseChapter whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseChapter wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseChapter whereUpdatedAt($value)
 * @mixin IdeHelperCourseChapter
 */
class CourseChapter extends KeelearningModel
{
    use Duplicatable;
    use Saferemovable;
    use Translatable;
    use HasFactory;

    public $translated = ['title'];

    /**
     * @return BelongsTo
     */
    public function course()
    {
        return $this->belongsTo(Course::class)->withTemplates();
    }

    public function contents()
    {
        return $this->hasMany(CourseContent::class, 'course_chapter_id')->orderBy('position');
    }
}
