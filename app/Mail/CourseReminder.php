<?php

namespace App\Mail;

use App\Models\Courses\Course;
use App\Models\User;
use App\Services\Courses\CoursesEngine;
use App\Services\QueuePriority;
use App\Traits\CustomMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class CourseReminder extends KeelearningNotification
{
    use Queueable, SerializesModels;
    use CustomMail;

    public static array $availableTags = [
        'coursename',
        'active_until',
        'course-link',
        'mandatory-or-optional',
    ];
    public static array $requiredTags = [];

    /**
     * CourseReminder constructor.
     * @param Course $course
     * @param User $user
     */
    public function __construct(Course $course, User $user, CoursesEngine $coursesEngine)
    {
        parent::__construct();

        $course->setLanguage($user->getLanguage());

        $appProfile = $user->getAppProfile();
        $coursesEngine->attachUserParticipations(collect([$course]), $user);
        $participation = $coursesEngine->getLastParticipation($course, $user);
        $activeUntil = null;
        if($participation) {
            $activeUntil = $participation->availableUntil();
        } elseif ($course->duration_type == Course::DURATION_TYPE_FIXED) {
            $activeUntil = $course->available_until;
        }
        $mandatoryOrOptionalText = __($course->is_mandatory ? 'course.mandatory' : 'course.optional', [], $user->getLanguage());

        $this->app = $user->app;

        $this->data = [
            'coursename' => $course->title,
            'courseId' => $course->id,
            'active_until' => $activeUntil ? $activeUntil->format('d.m.Y') : '-',
            'course-link' => $appProfile->app_hosted_at . $course->getCoursePath(),
            'mandatory-or-optional' => $mandatoryOrOptionalText,
        ];
        $this->queue = QueuePriority::LOW;
        $this->recipient = $user;
        $this->pushNotificationMessage = __('notifications.course_reminder', $this->data, $user->getLanguage());
        $this->pushNotificationData = ['open_url' => '/courses/' . $course->id];
    }
}
