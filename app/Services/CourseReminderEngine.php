<?php

namespace App\Services;

use App\Mail\Mailer;
use App\Models\Courses\Course;
use App\Models\Courses\CourseParticipation;
use App\Models\Reminder;
use App\Models\ReminderMetadata;
use App\Services\Courses\CourseStatisticsEngine;
use Carbon\Carbon;

class CourseReminderEngine
{
    /**
     * @var \Illuminate\Foundation\Application|mixed|null
     */
    protected $mailer = null;

    /**
     * ReminderEngine constructor.
     */
    public function __construct()
    {
        $this->mailer = app(Mailer::class);
    }

    /**
     * Handles reminder.
     * @param Reminder $reminder
     * @return bool
     */
    public function handleReminder(Reminder $reminder): bool
    {
        $course = Course::withTemplates()->with('participations')->find($reminder->foreign_id);

        if (!$course->visible) {
            return false;
        }

        if ($course->is_template) {
            return false;
        }

        if ($course->duration_type == Course::DURATION_TYPE_FIXED) {
            return $this->handleFixedReminder($course, $reminder);
        }

        if ($course->duration_type == Course::DURATION_TYPE_DYNAMIC) {
            return $this->handleDynamicReminder($course, $reminder);
        }

        return false;
    }

    /**
     * Handles reminder for fixed duration courses.
     * @param Course $course
     * @param Reminder $reminder
     * @return bool
     */
    public function handleFixedReminder(Course $course, Reminder $reminder): bool
    {
        if (!$course->available_until) {
            return false;
        }

        // we're being passed all reminders, check here if we should send something out today
        if ($course->available_until->subDays($reminder->days_offset)->format('Y-m-d') != Carbon::now()->format('Y-m-d')) {
            return false;
        }

        if ($reminder->type === Reminder::TYPE_USER_COURSE_NOTIFICATION) {
            $courseStatisticsEngine = app(CourseStatisticsEngine::class);
            $users = $courseStatisticsEngine
                ->getCourseEligibleUsersQuery($course, $reminder->user)
                ->get();

            $this->sendNotifications($users, $course);
        } elseif ($reminder->type === Reminder::TYPE_ADMIN_COURSE_NOTIFICATION) {
            $this->sendCourseResults($reminder, $course);
        }

        return true;
    }

    /**
     * Handles reminder for dynamic duration courses.
     * @param Course $course
     * @param Reminder $reminder
     * @return bool
     */
    public function handleDynamicReminder(Course $course, Reminder $reminder): bool
    {
        if ($reminder->type === Reminder::TYPE_ADMIN_COURSE_NOTIFICATION) {
            return false;
        }
        if ($reminder->type === Reminder::TYPE_USER_COURSE_NOTIFICATION) {
            $courseStatisticsEngine = app(CourseStatisticsEngine::class);
            $users = $courseStatisticsEngine
                ->getCourseEligibleUsersQuery($course, $reminder->user)
                ->get();

            // get participations that end in $reminder->days_offset days
            $startDay = Carbon::today()->startOfDay()->addDays($reminder->days_offset);
            switch ($course->participation_duration_type) {
                case Course::PARTICIPATION_DURATION_DAYS:
                    $startDay->subDays($course->participation_duration);
                    break;
                case Course::PARTICIPATION_DURATION_WEEKS:
                    $startDay->subWeeks($course->participation_duration);
                    break;
                case Course::PARTICIPATION_DURATION_MONTHS:
                    $startDay->subMonths($course->participation_duration);
                    break;
            }

            $participatingUserIds = CourseParticipation::whereIn('user_id', $users->pluck('id'))
                ->where('created_at', '>=', $startDay)
                ->whereRaw('created_at < ? + INTERVAL 1 DAY', [$startDay])
                ->pluck('user_id');

            $users = $users->whereIn('id', $participatingUserIds);

            $this->sendNotifications($users, $course);
            return true;
        }
        return false;
    }

    /**
     * Sends notifications to users email.
     * @param $users
     * @param Course $course
     */
    public function sendNotifications($users, Course $course)
    {
        $users = $users->filter(function ($user) use ($course) {
            return ! $course->participations
                ->where('user_id', $user->id)
                ->where('passed', 1)
                ->count();
        });

        foreach ($users as $user) {
            $this->mailer->sendCourseReminder($user, $course);
        }
    }

    /**
     * @param Reminder $reminder
     * @param Course $course
     */
    public function sendCourseResults(Reminder $reminder, Course $course)
    {
        $emails = $reminder
            ->metadata()
            ->where('key', 'email')
            ->pluck('value');
        $appSettings = new AppSettings($reminder->app_id);
        $hasPersonalData = !$appSettings->getValue('hide_personal_data');
        $hasPersonalDataExternal = $hasPersonalData && !$appSettings->getValue('hide_personal_data_for_external_users');
        $hasEmails = $hasPersonalData && !$appSettings->getValue('hide_emails_backend');
        $hasEmailsExternal = $hasPersonalDataExternal && !$appSettings->getValue('hide_emails_backend');

        foreach ($emails as $email) {
            $this->mailer->sendCourseResults($email, $course, $hasPersonalDataExternal, $hasEmailsExternal);
        }

        foreach ($course->managers as $manager) {
            $showPersonalData = $hasPersonalData && $manager->hasRight('courses-personaldata');
            $showEmails = $hasEmails && $manager->hasRight('courses-showemails');

            if($manager->email) {
                $this->mailer->sendCourseResults($manager->email, $course, $showPersonalData, $showEmails, $manager);
            }
        }
    }

    /**
     * Updates the reminder emails of the course.
     *
     * @param int $courseId
     * @param array $emails
     */
    public function updateEmails(int $courseId, array $emails) {
        $reminders = Reminder
            ::where('foreign_id', $courseId)
            ->where('type', Reminder::TYPE_ADMIN_COURSE_NOTIFICATION)
            ->get();

        ReminderMetadata
            ::whereIn('reminder_id', $reminders->pluck('id'))
            ->where('key', 'email')
            ->delete();

        foreach ($reminders as $reminder) {
            foreach ($emails as $email) {
                $metadata = new ReminderMetadata();
                $metadata->reminder_id = $reminder->id;
                $metadata->key = 'email';
                $metadata->value = $email;
                $metadata->save();
            }
        }
    }
}
