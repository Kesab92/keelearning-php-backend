<?php


namespace App\Services\AccessLogMeta\Courses;

use App\Services\AccessLogMeta\AccessLogMeta;

class AccessLogCourseChapterDelete implements AccessLogMeta
{
    /**
     * Deleted object
     * @var null
     */
    protected $chapter = null;

    public function __construct($chapter)
    {
        $this->chapter = $chapter;
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        return [
            'course_id' => $this->chapter->course_id,
            'course_title' => $this->chapter->course->title,
            'chapter_id' => $this->chapter->id,
            'chapter_title' => $this->chapter->title,
        ];
    }

    /**
     * @param $meta
     * @return string
     */
    public static function displayMeta($meta)
    {
        return view('access-logs.types.courses.deleteChapter', [
            'meta' => $meta
        ]);
    }
}
