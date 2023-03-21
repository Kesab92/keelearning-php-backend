<?php
namespace App\Removers;

use App\Models\Courses\CourseChapter;
use App\Models\Courses\CourseContent;
use App\Models\Courses\CourseContentAttempt;
use App\Models\Courses\CourseContentAttemptAttachment;
use Illuminate\Database\Eloquent\Builder;

class CourseChapterRemover extends Remover
{
    /**
    * Deletes/Resets everything depending on the course chapter
    *
    */
    protected function deleteDependees()
    {
        /** @var CourseChapter $chapter */
        $chapter = $this->object;
        $this->removeParticipationData();
        foreach($chapter->contents as $content) {
            $content->safeRemove();
        }
    }

    private function removeParticipationData()
    {
        /** @var CourseChapter $chapter */
        $chapter = $this->object;
        $contentIds = $chapter->contents()->pluck('id');
        CourseContentAttemptAttachment
            ::whereHas('attempt', function (Builder $query) use ($contentIds) {
                $query->whereIn('course_content_id', $contentIds);
            })->delete();
        CourseContentAttempt
            ::whereIn('course_content_id', $contentIds)
            ->delete();
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
        $messages = [];

        /** @var CourseChapter $chapter */
        $chapter = $this->object;

        // Now the frontend needs at least one chapter to work correctly
        // It should be fixed while relaunch refactoring
        if($chapter->course->chapters->count() == 1) {
            $messages[] = 'Das letzte Kapitel in einem Kurs kann nicht gelöscht werden.';
        }

        return count($messages) ? $messages : false;
    }

    /**
     * Gets amount of dependees that will be deleted/altered
     *
     * @return boolean|array false if clear of dependees, array of counts if not
     */
    public function getDependees()
    {
        /** @var CourseChapter $chapter */
        $chapter = $this->object;
        $certificates = CourseContentAttempt
            ::where('passed', 1)
            ->whereHas('content', function(Builder $query) use ($chapter) {
                $query
                    ->where('course_chapter_id', $chapter->id)
                    ->where('type', CourseContent::TYPE_CERTIFICATE);
            })
            ->count();

        $tests = CourseContentAttempt
            ::whereHas('content', function(Builder $query) use ($chapter) {
                $query
                    ->where('course_chapter_id', $chapter->id)
                    ->where('type', CourseContent::TYPE_QUESTIONS)
                    ->where('is_test', 1);
            })
            ->count();

        return [
            'Erhaltene Zertifikate' => $certificates,
            'Abgelegte Tests' => $tests,
            'Kursinhalte' => $chapter->contents()->count(),
        ];
    }
}
