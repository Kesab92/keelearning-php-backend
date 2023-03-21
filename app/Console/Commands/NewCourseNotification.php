<?php

namespace App\Console\Commands;

use App\Mail\Mailer;
use App\Models\Courses\Course;
use App\Services\Courses\CoursesEngine;
use App\Services\Courses\CourseStatisticsEngine;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class NewCourseNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:newcoursenotification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends notifications about new courses';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(CoursesEngine $coursesEngine)
    {
        $this->info('Checking for due notifications');

        $courses = Course
            ::where('send_new_course_notification', 1)
            ->where('visible', 1)
            ->whereDate('available_from', Carbon::today())
            ->with('participations')
            ->get();

        $this->info('Courses found: '.count($courses));

        foreach ($courses as $course) {
            $coursesEngine->notifyAboutNewCourse($course);
            $this->line('Sent notifications for the course #'.$course->id);
        }

        $this->info('Finished sending notifications');

        return 0;
    }
}
