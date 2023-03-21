<?php

namespace App\Removers\Traits;

use App\Models\Courses\CourseContent;

trait CourseDependencyMessage
{

    private function getCourseMessages(int $type, int $foreignId):array
    {
        $messages = [];
        $courseContents = CourseContent
            ::where('type', $type)
            ->where('foreign_id', $foreignId)
            ->with('chapter.course')
            ->get();

        foreach ($courseContents as $courseContent) {
            if (! $courseContent->chapter || ! $courseContent->chapter->course) {
                continue;
            }
            $messages[] = 'Kurs: '.$courseContent->chapter->course->title.' - bitte den Inhalt "'.$courseContent->title.'" aus Kapitel "'.$courseContent->chapter->title.'" entfernen.';
        }

        return $messages;
    }
}
