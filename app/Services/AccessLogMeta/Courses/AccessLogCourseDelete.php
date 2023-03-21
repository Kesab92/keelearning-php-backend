<?php


namespace App\Services\AccessLogMeta\Courses;

use App\Services\AccessLogMeta\AccessLogMeta;

class AccessLogCourseDelete implements AccessLogMeta
{
    /**
     * Deleted object
     * @var null
     */
    protected $course = null;

    public function __construct($course)
    {
        $this->course = $course;
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        return [
            'course_id' => $this->course->id,
            'course_title' => $this->course->title,
        ];
    }

    /**
     * @param $meta
     * @return string
     */
    public static function displayMeta($meta)
    {
        return view('access-logs.types.courses.delete', [
            'meta' => $meta
        ]);
    }
}
