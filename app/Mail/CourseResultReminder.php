<?php

namespace App\Mail;

use App\Exports\CourseStatistics;
use App\Models\Courses\Course;
use App\Models\User;
use App\Services\QueuePriority;
use App\Traits\CustomMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class CourseResultReminder extends KeelearningNotification
{
    use Queueable, SerializesModels;
    use CustomMail{
        build as buildBase;
    }

    protected $course = null;
    protected bool $showPersonalData;
    protected bool $showEmails;

    public static array $availableTags = [
        'coursename',
        'course-end',
        'course-link',
        'mandatory-or-optional',
    ];
    public static array $requiredTags = [];
    protected bool $isAlwaysActive = true;

    /**
     * CourseResultReminder constructor.
     *
     * @param Course $course
     * @param User $manager
     * @param boolean $showPersonalData
     * @param boolean $showEmails
     */
    public function __construct(Course $course, ?User $manager, bool $showPersonalData, bool $showEmails)
    {
        parent::__construct();

        $appId = $course->app->id;
        $mandatoryOrOptionalText = $course->is_mandatory ? __('course.mandatory', [], defaultAppLanguage($appId)) : __('course.optional', [], defaultAppLanguage($appId));
        if($manager) {
            $courseLink = $manager->getAppProfile()->app_hosted_at .'/courses/' . $course->id;
        } else {
            $courseLink = $course->app->getDefaultAppProfile()->app_hosted_at . '/courses/' . $course->id;
        }
        $this->data = [
            'coursename' => $course->title,
            'courseId' => $course->id,
            'course-end' => $course->available_until ? $course->available_until->format('d.m.Y H:i') : '-',
            'course-link' => $courseLink,
            'mandatory-or-optional' => $mandatoryOrOptionalText,
        ];
        $this->app = $course->app;
        $this->course = $course;
        $this->showPersonalData = $showPersonalData;
        $this->showEmails = $showEmails;
        $this->queue = QueuePriority::LOW;
    }

    /**
     * Builds the mail.
     * @return CourseResultReminder
     */
    public function build()
    {
        $filename = 'ergebnis-' . Str::slug($this->course->title) . '.xlsx';

        return $this->buildBase()
            ->attach($this->createAttachment($filename));
    }

    /**
     * Creates an attachment for the email.
     * @param $filename
     */
    public function createAttachment($filename)
    {
        $filepath = sys_get_temp_dir() . '/' . $filename;
        file_put_contents($filepath, Excel::raw(new CourseStatistics($this->course->id, null, $this->showPersonalData, $this->showEmails), \Maatwebsite\Excel\Excel::XLSX));

        return $filepath;
    }
}
