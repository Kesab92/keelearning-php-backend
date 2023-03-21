<?php
namespace App\Removers;

use App\Models\Comments\Comment;
use App\Models\Comments\CommentReport;
use App\Models\Courses\Course;
use App\Models\Courses\CourseContent;
use App\Models\Courses\CourseContentAttempt;
use App\Models\Courses\CourseContentAttemptAttachment;
use App\Models\Reminder;
use App\Services\MorphTypes;
use Illuminate\Database\Eloquent\Builder;

class CourseRemover extends Remover
{
    /**
    * Deletes/Resets everything depending on the course
    *
    */
    protected function deleteDependees()
    {
        /** @var Course $course */
        $course = $this->object;

        CommentReport::whereIn('comment_id', $course->comments->pluck('id'))->delete();
        Reminder
            ::whereIn('type', Reminder::TYPES[MorphTypes::TYPE_COURSE])
            ->where('foreign_id', $course->id)
            ->delete();
        $course->comments()->delete();

        $this->removeParticipations();
        $course->categories()->detach();

        foreach($course->chapters as $chapter) {
            $chapter->safeRemove();
        }
    }

    private function removeParticipations()
    {
        /** @var Course $course */
        $course = $this->object;
        CourseContentAttemptAttachment::whereHas('attempt.participation', function (Builder $query) use ($course) {
            $query->where('course_id', $course->id);
        })->delete();
        $courseContentAttemptIds = CourseContentAttempt::whereHas('participation', function (Builder $query) use ($course) {
            $query->where('course_id', $course->id);
        })->pluck('id');
        Comment::where('foreign_type', MorphTypes::TYPE_COURSE_CONTENT_ATTEMPT)
            ->whereIn('foreign_id', $courseContentAttemptIds)
            ->delete();
        CourseContentAttempt::whereIn('id', $courseContentAttemptIds)->delete();
        $course->participations()->delete();
    }

    /**
    * Executes the actual deletion
    *
    * @return true
    */
    protected function doDeletion()
    {
        $this->deleteDependees();
        $this->object->delete();

        return true;
    }

    /*
     * @return false if clear of blocking dependees, array of strings if not
     */
    public function getBlockingDependees()
    {
        return false;
    }

    /**
     * Gets amount of dependees that will be deleted/altered
     *
     * @return boolean|array false if clear of dependees, array of counts if not
     */
    public function getDependees()
    {
        /** @var Course $course */
        $course = $this->object;

        $certificates = CourseContentAttempt
            ::where('passed', 1)
            ->whereHas('participation', function(Builder $query) use ($course) {
                $query->where('course_id', $course->id);
            })
            ->whereHas('content', function(Builder $query) {
                $query->where('type', CourseContent::TYPE_CERTIFICATE);
            })
            ->count();

        $tests = CourseContentAttempt
            ::whereHas('participation', function(Builder $query) use ($course) {
                $query->where('course_id', $course->id);
            })
            ->whereHas('content', function(Builder $query) {
                $query->where('type', CourseContent::TYPE_QUESTIONS)
                      ->where('is_test', 1);
            })
            ->count();

        $comments = $course->comments->count();
        $commentReports = CommentReport::whereIn('comment_id', $course->comments->pluck('id'))->count();

        return [
            'Kursteilnahmen' => $course->participations()->count(),
            'Erhaltene Zertifikate' => $certificates,
            'Abgelegte Tests' => $tests,
            'Kursinhalte' => $course->contents()->count(),
            'Kommentare' => $comments,
            'Kommentar Meldungen' => $commentReports,
        ];
    }
}
