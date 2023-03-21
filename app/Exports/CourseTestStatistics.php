<?php

namespace App\Exports;

use App\Models\Courses\Course;
use App\Models\Courses\CourseContent;
use App\Models\Courses\CourseContentAttempt;
use App\Models\Courses\CourseParticipation;
use App\Models\User;
use App\Services\Courses\CourseStatisticsEngine;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CourseTestStatistics implements FromView, ShouldAutoSize
{

    /** @var Course $content */
    private $course;
    /** @var CourseContent $content */
    private $content;
    private $courseStatisticsEngine;
    private $admin;
    private $showEmails;
    private $showPersonalData;

    public function __construct(int $courseId, int $contentId, User $admin, $showPersonalData = false, $showEmails = false)
    {
        $this->course = Course::findOrFail($courseId);
        $this->content = $this->course->contents()->where('course_contents.id', $contentId)->first();
        if(!$this->content) {
            app()->abort(404);
        }
        $this->admin = $admin;
        $this->showEmails = $showEmails;
        $this->showPersonalData = $showPersonalData;
        $this->courseStatisticsEngine = app(CourseStatisticsEngine::class);
    }

    public function view(): View
    {
        return view('stats.courses.csv.test', $this->getData());
    }

    private function getData() {
        $usersQuery = $this->courseStatisticsEngine->getCourseParticipatingUsersQuery($this->course, $this->admin);
        $entries = [];
        $usersQuery->chunk(1000, function($users) use (&$entries) {
            $participations = CourseParticipation
                ::with('user')
                ->where('course_id', $this->course->id)
                ->whereIn('user_id', $users->pluck('id'))
                ->get();
            $participations = $participations->groupBy('user_id');
            $participations->transform(function(Collection $participations) {
                return $participations->sortByDesc('id')->first();
            });
            $attempts = CourseContentAttempt
                ::whereIn('course_participation_id', $participations->pluck('id'))
                ->where('course_content_id', $this->content->id)
                ->get()
                ->groupBy('course_participation_id');
            foreach($participations as $participation) {
                $entries[] = $this->formatEntry($participation, $attempts->get($participation->id));
            }
        });

        return [
            'users' => $entries,
            'course' => $this->course,
            'showEmails' => $this->showEmails,
            'showPersonalData' => $this->showPersonalData,
        ];
    }

    /**
     * @param CourseParticipation $participation
     * @param CourseContentAttempt[]|Collection $attempts
     * @return array
     * @throws \Exception
     */
    private function formatEntry(CourseParticipation $participation, $attempts) {
        $entry = [
            'username' => $participation->user->username,
            'lastname' => $participation->user->lastname,
            'firstname' => $participation->user->firstname,
            'email' => $participation->user->email,
            'passed' => null,
            'finished_at' => null,
        ];
        if($attempts) {
            foreach($attempts as $attempt) {
                if($attempt->passed === 1) {
                    $entry['passed'] = 1;
                    $entry['finished_at'] = new Carbon($attempt->finished_at);
                    break;
                }
                if($entry['passed'] !== 1 && $attempt->passed === 0) {
                    $entry['passed'] = 0;
                    $entry['finished_at'] = new Carbon($attempt->finished_at);
                }
            }
        }

        return $entry;
    }
}
