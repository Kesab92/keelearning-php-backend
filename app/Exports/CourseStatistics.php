<?php

namespace App\Exports;

use App\Models\Courses\Course;
use App\Models\Courses\CourseParticipation;
use App\Models\User;
use App\Services\Courses\CourseStatisticsEngine;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CourseStatistics implements FromView, ShouldAutoSize
{

    private $course;
    private $courseStatisticsEngine;
    private $admin;
    private $showEmails;
    private $showPersonalData;

    public function __construct(int $courseId, User $admin = null, $showPersonalData = false, $showEmails = false)
    {
        $this->course = Course::findOrFail($courseId);

        if(!$admin) {
            $appId = $this->course->app_id;

            $this->course->setAppId($appId);
            foreach ($this->course->chapters as $chapter) {
                $chapter->setAppId($appId);
                foreach ($chapter->contents as $content) {
                    $content->setAppId($appId);
                    $content->setAppId($appId);
                }
            }
        }

        $this->admin = $admin;
        $this->showEmails = $showEmails;
        $this->showPersonalData = $showPersonalData;
        $this->courseStatisticsEngine = app(CourseStatisticsEngine::class);
    }

    public function view(): View
    {
        return view('stats.courses.csv.overall', $this->getData());
    }

    private function getData() {
        $usersQuery = $this->courseStatisticsEngine->getCourseParticipatingUsersQuery($this->course, $this->admin);
        $entries = collect();
        $usersQuery->chunk(1000, function($users) use ($entries) {
            $participations = CourseParticipation::with('user', 'contentAttempts')
                ->where('course_id', $this->course->id)
                ->whereIn('user_id', $users->pluck('id'))
                ->get();
            $participations = $participations->groupBy('user_id');
            $participations->transform(function(Collection $participations) {
                return $participations->sortByDesc('id')->first();
            });
            foreach($participations as $participation) {
                $entries->push($this->formatEntry($participation));
            }
        });

        return [
            'users' => $entries,
            'course' => $this->course,
            'contentCount' => $this->getContentCount(),
            'showEmails' => $this->showEmails,
            'showPersonalData' => $this->showPersonalData,
        ];
    }

    private function getContentCount() {
        $count = 0;
        foreach($this->course->chapters as $chapter) {
            foreach($chapter->contents as $content) {
                if(!$content->visible) {
                    continue;
                }
                $count++;
            }
        }
        return $count;
    }

    private function formatEntry(CourseParticipation $participation) {
        $attemptsByContent = $participation->contentAttempts->groupBy('course_content_id');
        $entry = [
            'username' => $participation->user->username,
            'lastname' => $participation->user->lastname,
            'firstname' => $participation->user->firstname,
            'active' => $participation->user->active,
            'email' => $participation->user->email,
            'passed' => $participation->passed,
            'finished_at' => new Carbon($participation->finished_at),
            'total_minutes' => 0,
        ];
        $passedCount = 0;
        foreach($this->course->chapters as $chapter) {
            foreach($chapter->contents as $content) {
                $hasPassed = null;
                if($attempts = $attemptsByContent->get($content->id)) {
                    $hasPassed = $attempts->where('passed', 1)->count() > 0;
                }
                $entry['content-' . $content->id] = $hasPassed;
                if($hasPassed) {
                    $entry['total_minutes'] += $content->duration;
                    if($content->visible) {
                        $passedCount++;
                    }
                }
            }
        }
        $entry['passedCount'] = $passedCount;

        return $entry;
    }
}
