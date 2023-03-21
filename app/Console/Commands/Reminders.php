<?php

namespace App\Console\Commands;

use App\Mail\Mailer;
use App\Models\Courses\Course;
use App\Models\Reminder;
use App\Services\CourseReminderEngine;
use App\Services\ReminderEngine;
use Carbon\Carbon;
use Illuminate\Console\Command;

class Reminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Looking for configured reminders and sends email to the user';

    const COURSE_REPETITION_THRESHOLD_DAYS = 14;

    /**
     * Look up for reminders and sends mail with queue.
     *
     * @param ReminderEngine $reminderEngine
     * @param CourseReminderEngine $courseReminderEngine
     */
    public function handle(ReminderEngine $reminderEngine, CourseReminderEngine $courseReminderEngine)
    {
        $this->info('Checking for due reminders');

        $reminders = Reminder::all();
        $this->info('Reminders found: '.count($reminders));
        foreach ($reminders as $reminder) {
            try {
                if(in_array($reminder->type, [Reminder::TYPE_USER_TEST_NOTIFICATION, Reminder::TYPE_TEST_RESULTS])) {
                    if ($reminderEngine->handleReminder($reminder)) {
                        $this->line('Sent reminder #'.$reminder->id);
                    }
                } elseif(in_array($reminder->type, [Reminder::TYPE_USER_COURSE_NOTIFICATION, Reminder::TYPE_ADMIN_COURSE_NOTIFICATION])) {
                    if ($courseReminderEngine->handleReminder($reminder)) {
                        $this->line('Sent reminder #'.$reminder->id);
                    }
                }
            } catch (\Exception $e) {
                \Sentry::captureException($e);
                $this->error('Couldnt send reminder #'.$reminder->id);
            }
        }

        $this->info('Checking for due repetition course reminders');

        $today = Carbon::today();
        $courses = Course
            ::repeatingTemplate()
            ->where('send_repetition_course_reminder', 1)
            ->get();

        $courses = $courses->filter(function($course) use ($today) {
            if(!$course->nextRepetitionDate || $course->nextRepetitionDate->diffInDays($today) <= self::COURSE_REPETITION_THRESHOLD_DAYS) {
                return false;
            }

            switch($course->repetition_interval_type) {
                case Course::INTERVAL_WEEKLY:
                    $days = $course->repetition_interval * 7;
                    break;
                case Course::INTERVAL_MONTHLY:
                    $days = $course->repetition_interval * 30;
                    break;
                default:
                    $days = 0;
                    break;
            }

            $days = floor($days / 12);

            if($days < self::COURSE_REPETITION_THRESHOLD_DAYS) {
                $days = self::COURSE_REPETITION_THRESHOLD_DAYS;
            }

            $reminderDate = $course->nextRepetitionDate->subDays($days);

            return $reminderDate->isSameDay($today);
        });

        $this->info('Repetition course reminders found: '.count($courses));

        $mailer = app(Mailer::class);

        foreach($courses as $course) {
            $emails = $course->managers->pluck('email');
            try {
                foreach ($emails as $email) {
                    $this->line('Sent repetition course reminder for course #' . $course->id);
                    $mailer->sendRepetitionCourseReminder($email, $course);
                }
            } catch (\Exception $e) {
                \Sentry::captureException($e);
                $this->error('Couldnt send repetition course reminder for course #' . $course->id);
            }
        }

        $this->info('Finished sending reminders');
    }
}
