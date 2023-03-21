<?php

namespace App\Mail;

use App\Models\Courses\Course;
use App\Models\User;
use App\Services\QueuePriority;
use App\Traits\CustomMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class CourseAccessRequest extends KeelearningNotification
{
    use Queueable, SerializesModels;
    use CustomMail;

    public static array $availableTags = [
        'tag-list',
        'user-link',
    ];
    public static array $requiredTags = [
        'course-link',
    ];
    protected bool $isAlwaysActive = true;

    /**
     * Create a new message instance.
     *
     * @param Course $course
     * @param User $user
     */
    public function __construct(Course $course, User $user)
    {
        parent::__construct();

        $course->setLanguage($user->getLanguage());

        $courseLink = '<a href="https://admin.keelearning.de/courses#/courses/' . $course->id . '/general">' . $course->title . '</a>';
        $userLink = '<a href="https://admin.keelearning.de/users#/users/' . $user->id . '/general">' . $user->username . '</a>';
        $tagList = $course->tags()->pluck('label')->join(', ');

        $this->app = $user->app;
        $this->data = [
            'course-link' => $courseLink,
            'user-link' => $userLink,
            'tag-list' => $tagList,
        ];;
        $this->forceAppProfile = $user->getAppProfile();
        $this->queue = QueuePriority::LOW;
        $this->recipient = $user;
    }

    public function getCustomViewData()
    {
        return [
            'hideEncoding' => true,
        ];
    }
}
