<?php

namespace App\Console\Commands;

use App\Models\App;
use App\Models\AppProfile;
use App\Models\Courses\Course;
use App\Models\Courses\CourseContentAttempt;
use App\Models\Courses\CourseParticipation;
use App\Models\Page;
use App\Models\User;
use App\Samba\Data\CreateAccount;
use App\Samba\Samba;
use App\Services\Courses\CourseStatisticsEngine;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateCourseParticipations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:courseparticipations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates participations for a course to test performance';
    /**
     * @var CourseStatisticsEngine
     */
    private CourseStatisticsEngine $courseStatisticsEngine;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(CourseStatisticsEngine $courseStatisticsEngine)
    {
        parent::__construct();
        $this->courseStatisticsEngine = $courseStatisticsEngine;
    }

    /**
     *  Creates a new app with all necessary additional data.
     */
    public function handle()
    {
        $courseId = $this->ask('Gimme the id of the course');
        $course = Course::findOrFail($courseId);
        $users = User::where('app_id', $course->app_id)->get();
        $bar = $this->output->createProgressBar($users->count());
        foreach($users as $user) {
            $participation = new CourseParticipation();
            $participation->user_id = $user->id;
            $participation->course_id = $course->id;
            $participation->save();
            foreach($course->chapters as $chapter) {
                foreach($chapter->contents as $content) {
                    $contentAttempt = new CourseContentAttempt();
                    $contentAttempt->course_participation_id = $participation->id;
                    $contentAttempt->course_content_id = $content->id;
                    $contentAttempt->passed = 1;
                    $contentAttempt->finished_at = Carbon::now();
                    $contentAttempt->save();
                }
            }
            $bar->advance();
        }
        $this->info('Done :)');
    }
}
