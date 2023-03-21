<?php

namespace App\Mail;

use App\Models\Courses\Course;
use App\Models\User;
use App\Services\QueuePriority;
use App\Traits\CustomMail;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class NewCourseNotification extends KeelearningNotification
{
    use Queueable, SerializesModels;
    use CustomMail{
        build as buildBase;
    }

    public static array $availableTags = [
        'coursename',
        'course-duration',
        'course-link',
        'mandatory-or-optional',
    ];
    public static array $requiredTags = [];

    /**
     * CourseReminder constructor.
     * @param $data
     * @param $user
     */
    public function __construct(Course $course, User $user)
    {
        parent::__construct();

        $course->setLanguage($user->getLanguage());

        $appProfile = $user->getAppProfile();
        $mandatoryOrOptionalText = __($course->is_mandatory ? 'course.singular_mandatory' : 'course.singular_optional', [], $user->getLanguage());

        if ($course->duration_type == Course::DURATION_TYPE_FIXED && $course->available_until) {
            $daysLeft = $course->available_until->diffInDays(Carbon::today());
            $courseDuration = trans_choice('course.days_left', $daysLeft, ['days' => $daysLeft], $user->getLanguage());
        } elseif($course->duration_type == Course::DURATION_TYPE_DYNAMIC) {
            $courseDuration = trans_choice('course.time_from_start_' . $course->participation_duration_type, $course->participation_duration, ['number' => $course->participation_duration], $user->getLanguage());
        } else {
            $courseDuration = __('course.unlimited', [], $user->getLanguage());
        }

        $this->app = $user->app;
        $this->data = [
            'coursename' => $course->title,
            'courseId' => $course->id,
            'course-duration' => $courseDuration,
            'course-link' => $appProfile->app_hosted_at . $course->getCoursePath(),
            'mandatory-or-optional' => $mandatoryOrOptionalText,
        ];
        $this->queue = QueuePriority::LOW;
        $this->recipient = $user;
        $this->pushNotificationMessage = __('notifications.new_course', $this->data, $user->getLanguage());
        $this->pushNotificationData = ['open_url' => '/courses/' . $course->id];
    }

    /**
     * Builds the mail.
     * @return NewCourseNotification
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
