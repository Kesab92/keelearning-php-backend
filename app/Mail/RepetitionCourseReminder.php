<?php

namespace App\Mail;

use App\Models\Courses\Course;
use App\Services\QueuePriority;
use App\Traits\CustomMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class RepetitionCourseReminder extends KeelearningNotification
{
    use Queueable, SerializesModels;
    use CustomMail{
        build as buildBase;
    }

    protected $course = null;

    public static array $availableTags = [
        'coursename',
        'repetition-date',
        'course-template-link',
        'mandatory-or-optional',
    ];
    public static array $requiredTags = [];
    protected bool $isAlwaysActive = true;

    /**
     * RepetitionCourseReminder constructor.
     * @param Course $course
     */
    public function __construct(Course $course)
    {
        parent::__construct();

        $appId = $course->app_id;
        $mandatoryOrOptionalText = $course->is_mandatory ? __('course.singular_mandatory', [], defaultAppLanguage($appId)) : __('course.singular_optional', [], defaultAppLanguage($appId));

        $this->data = [
            'coursename' => $course->title,
            'courseId' => $course->id,
            'repetition-date' => $course->nextRepetitionDate->format('d.m.Y H:i'),
            'course-template-link' => 'https://admin.keelearning.de/courses#/courses/' . $course->id . '/general',
            'mandatory-or-optional' => $mandatoryOrOptionalText,
        ];
        $this->app = $course->app;
        $this->course = $course;
        $this->queue = QueuePriority::LOW;
    }

    /**
     * Builds the mail.
     * @return RepetitionCourseReminder
     */
    public function build()
    {
        $this->withSwiftMessage(function ($message) {
            $message->courseId = $this->data['courseId'];
            $message->mailClass = self::class;
        });

        return $this->buildBase();
    }
}
