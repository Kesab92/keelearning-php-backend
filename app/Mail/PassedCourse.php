<?php

namespace App\Mail;

use App\Models\Courses\Course;
use App\Models\Courses\CourseParticipation;
use App\Models\User;
use App\Services\QueuePriority;
use App\Traits\CustomMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class PassedCourse extends KeelearningNotification
{
    use Queueable, SerializesModels;
    use CustomMail{
        build as buildBase;
    }

    public static array $availableTags = [
        'coursename',
        'passed-date',
        'app-course-link',
    ];
    public static array $requiredTags = [];

    /**
     * PassedCourse constructor.
     * @param Course $course
     * @param CourseParticipation $participation
     * @param User $user
     */
    public function __construct(Course $course, CourseParticipation $participation, User $user)
    {
        parent::__construct();

        $course->setLanguage($user->getLanguage());

        $appProfile = $user->getAppProfile();

        $this->app = $user->app;
        $this->data = [
            'coursename' => $course->title,
            'courseId' => $course->id,
            'passed-date' => $participation->finished_at ? $participation->finished_at->format('d.m.Y') : '-',
            'app-course-link' => $appProfile->app_hosted_at . $course->getCoursePath(),
        ];
        $this->queue = QueuePriority::LOW;
        $this->recipient = $user;
        $this->pushNotificationMessage = __('notifications.course_passed', ['name' => $course->title], $user->getLanguage());
    }

    /**
     * Builds the mail.
     * @return PassedCourse
     */
    public function build()
    {
        $this->withSwiftMessage(function ($message) {
            $message->user = $this->recipient;
            $message->courseId = $this->data['courseId'];
            $message->mailClass = self::class;
        });

        return $this->buildBase();
    }
}
