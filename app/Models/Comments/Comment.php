<?php

namespace App\Models\Comments;

use App\Models\App;
use App\Models\Courses\Course;
use App\Models\Courses\CourseContentAttempt;
use App\Models\KeelearningModel;
use App\Models\LearningMaterial;
use App\Models\News;
use App\Models\User;
use App\Services\MorphTypes;
use Illuminate\Support\Collection;

/**
 * Class Comment
 *
 * @package App\Models\Comments
 * @property int $id
 * @property int $app_id
 * @property int $author_id
 * @property int $foreign_type
 * @property int $foreign_id
 * @property int $parent_id
 * @property int $deleted_by_id
 * @property string $body
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property Collection $reports
 * @property App $app
 * @property User $author
 * @property User $deletedBy
 * @property Collection $commentable
 * @property-read int|null $reports_count
 * @method static \Illuminate\Database\Eloquent\Builder|Comment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Comment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Comment query()
 * @method static \Illuminate\Database\Eloquent\Builder|Comment whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Comment whereAuthorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Comment whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Comment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Comment whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Comment whereDeletedById($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Comment whereForeignId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Comment whereForeignType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Comment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Comment whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Comment whereUpdatedAt($value)
 * @mixin \Eloquent
 * @mixin IdeHelperComment
 */
class Comment extends KeelearningModel
{
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    const COMMENTABLES = [
        MorphTypes::TYPE_COURSE,
        MorphTypes::TYPE_LEARNINGMATERIAL,
        MorphTypes::TYPE_NEWS,
        // + Course Content Attempts
    ];

    public function app()
    {
        return $this->belongsTo(App::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class);
    }

    public function parentComment()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class);
    }

    public function reports()
    {
        return $this->hasMany(CommentReport::class);
    }

    public function attachments()
    {
        return $this->hasMany(CommentAttachment::class);
    }

    public function commentable()
    {
        return $this
            ->morphTo('commentable', 'foreign_type', 'foreign_id');
    }

    /**
     * Limits the query to the scope of comments of the app with the given id.
     *
     * @param $query
     * @param $appId
     * @return mixed
     */
    public function scopeOfApp($query, $appId)
    {
        return $query->where('app_id', '=', $appId);
    }

    public function getContentFrontendUrl(): string {
        $url = '';
        switch ($this->foreign_type) {
            case MorphTypes::TYPE_NEWS:
                $url = '/news/' . $this->foreign_id;
                break;
            case MorphTypes::TYPE_COURSE:
                $url = '/courses/' . $this->foreign_id;
                break;
            case MorphTypes::TYPE_LEARNINGMATERIAL:
                $url = '/learningmaterials/' . $this->foreign_id;
                break;
            case MorphTypes::TYPE_COURSE_CONTENT_ATTEMPT:
                $courseContentAttempt = CourseContentAttempt::find($this->foreign_id);
                if(!$courseContentAttempt) {
                    \Sentry::captureMessage('Course content attempt not found \'' . $this->foreign_id . '\'');
                    break;
                }
                $url = '/courses/' . $courseContentAttempt->content->chapter->course_id . '/participation/' . $courseContentAttempt->course_participation_id . '/content/' . $courseContentAttempt->course_content_id;
                break;
            default:
                \Sentry::captureMessage('Comment has the wrong type \'' . $this->foreign_type . '\'');
                break;
        }

        return $url;
    }

    public function getContentBackendUrl(): string {
        $url = '';
        switch ($this->foreign_type) {
            case MorphTypes::TYPE_NEWS:
                $url = '/comments#/comments/news/' . $this->foreign_id . '/comments/' . $this->id;
                break;
            case MorphTypes::TYPE_COURSE:
                $url = '/comments#/comments/courses/' . $this->foreign_id . '/comments/' . $this->id;
                break;
            case MorphTypes::TYPE_LEARNINGMATERIAL:
                $learningMaterial = LearningMaterial::find($this->foreign_id);
                if(!$learningMaterial) {
                    \Sentry::captureMessage('Learning material not found \'' . $this->foreign_id . '\'');
                    break;
                }
                $url = '/comments#/comments/learningmaterials/' . $learningMaterial->learning_material_folder_id . '/' . $learningMaterial->id . '/comments/' . $this->id;
                break;
            case MorphTypes::TYPE_COURSE_CONTENT_ATTEMPT:
                $courseContentAttempt = CourseContentAttempt::find($this->foreign_id);
                if(!$courseContentAttempt) {
                    \Sentry::captureMessage('Course content attempt not found \'' . $this->foreign_id . '\'');
                    break;
                }
                $url = '/comments#/comments/courses/' . $courseContentAttempt->content->chapter->course_id . '/todolists/' . $courseContentAttempt->course_content_id . '/' . $courseContentAttempt->course_participation_id;
                break;
            default:
                \Sentry::captureMessage('Comment has the wrong type \'' . $this->foreign_type . '\'');
                break;
        }

        return $url;
    }

    public function getContentTitle(): string {
        $title = '';

        switch ($this->foreign_type) {
            case MorphTypes::TYPE_COURSE:
                $course = Course::findOrFail($this->foreign_id);
                $title = $course->title;
                break;
            case MorphTypes::TYPE_LEARNINGMATERIAL:
                $learningMaterial = LearningMaterial::findOrFail($this->foreign_id);
                $title = $learningMaterial->title;
                break;
            case MorphTypes::TYPE_NEWS:
                $news = News::findOrFail($this->foreign_id);
                $title = $news->title;
                break;
            case MorphTypes::TYPE_COURSE_CONTENT_ATTEMPT:
                $courseContentAttempt = CourseContentAttempt::findOrFail($this->foreign_id);
                $title = $courseContentAttempt->content->course->title;
                break;
        }

        return $title;
    }
}
