<?php

namespace App\Models\Courses;

use App\Models\KeelearningModel;
use App\Traits\Saferemovable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use URL;

/**
 * App\Models\Courses\CourseContentAttempt
 *
 * @property int $id
 * @property int $course_content_id
 * @property int $course_participation_id
 * @property \Illuminate\Support\Carbon|null $finished_at
 * @property int|null $passed
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Courses\CourseContentAttemptAttachment[] $attachments
 * @property-read int|null $attachments_count
 * @property-read \App\Models\Courses\CourseContent $content
 * @property-read mixed $certificate_download_url
 * @property-read \App\Models\Courses\CourseParticipation $participation
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttempt newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttempt newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttempt query()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttempt whereCourseContentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttempt whereCourseParticipationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttempt whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttempt whereFinishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttempt whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttempt wherePassed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttempt whereUpdatedAt($value)
 * @mixin IdeHelperCourseContentAttempt
 */
class CourseContentAttempt extends KeelearningModel
{
    use HasFactory;
    use Saferemovable;

    protected $dates = [
        'created_at',
        'updated_at',
        'finished_at',
    ];

    /**
     * @return BelongsTo
     */
    public function content()
    {
        return $this->belongsTo(CourseContent::class, 'course_content_id');
    }

    public function participation()
    {
        return $this->belongsTo(CourseParticipation::class, 'course_participation_id');
    }

    public function attachments()
    {
        return $this->hasMany(CourseContentAttemptAttachment::class);
    }

    public function getCertificateDownloadUrlAttribute()
    {
        if (! $this->passed) {
            return null;
        }
        if ($this->content->type !== CourseContent::TYPE_CERTIFICATE) {
            return null;
        }

        return URL::signedRoute('courseCertificateDownload', [
            'course_id' => $this->content->chapter->course_id,
            'participation_id' => $this->course_participation_id,
            'attempt_id' => $this->id,
        ]);
    }

    public function getBackendCertificateDownloadUrlAttribute()
    {
        if (! $this->passed) {
            return null;
        }
        if ($this->content->type !== CourseContent::TYPE_CERTIFICATE) {
            return null;
        }

        return URL::route('courseCertificateDownloadInBackend', [
            'course_id' => $this->content->chapter->course_id,
            'participation_id' => $this->course_participation_id,
            'attempt_id' => $this->id,
        ]);
    }
}
