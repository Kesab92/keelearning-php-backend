<?php

namespace App\Exports;

use App\Models\Courses\Course;
use App\Models\Courses\CourseContent;
use App\Models\User;
use App\Services\WbtEngine;
use Carbon\Carbon;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CourseWBTStatistics implements FromView, ShouldAutoSize
{

    /** @var Course $content */
    private $course;
    /** @var CourseContent $content */
    private $content;
    private $adminUser;
    private $showEmails;
    private $showPersonalData;

    public function __construct($courseId, $contentId, $adminUser = null, $showPersonalData = false, $showEmails = false)
    {
        $this->course = Course::findOrFail($courseId);
        $this->content = $this->course->contents()->where('course_contents.id', $contentId)->first();
        if(!$this->content) {
            app()->abort(404);
        }
        $this->adminUser = $adminUser;
        $this->showEmails = $showEmails;
        $this->showPersonalData = $showPersonalData;
    }

    public function view(): View
    {
        return view('stats.courses.csv.wbt', $this->getData());
    }

    private function getData() {
        if(!$this->content->foreign_id) {
            app()->abort(404);
        }

        $events = $this->fetchAllEvents();

        $users = User::where('app_id', $this->course->app_id)
            ->whereIn('id', $events->pluck('user_id'))
            ->get()
            ->keyBy('id');

        $events->transform(function($event) {
            if(isset($event['user_id']) && $event['user_id']) {
                $event['user_id'] = (int)$event['user_id'];
            }
            if(isset($event['date']) && $event['date']) {
                $event['date'] = (new Carbon($event['date']))->format('Y-m-d H:i:s');
            }
            if(isset($event['score']) && $event['score']) {
                $event['score'] = (round($event['score'] * 10000) / 100) . '%';
            }
            return $event;
        });

        return [
            'events' => $events,
            'users' => $users,
            'course' => $this->course,
            'showEmails' => $this->showEmails,
            'showPersonalData' => $this->showPersonalData,
        ];
    }

    /**
     * Fetches all events from the database
     *
     * @return \Illuminate\Support\Collection
     */
    private function fetchAllEvents()
    {
        $events = collect([]);
        $wbtEngine = app(WbtEngine::class);

        $page = 0;
        while(true) {
            $eventChunk = $wbtEngine->getEvents(
                null,
                [$this->content->foreign_id],
                $this->course->id,
                $this->adminUser,
                null,
                false,
                $page,
                WbtEngine::ROWS_MAX,
                $this->showPersonalData
            );
            $events = $events->concat($eventChunk->get('events'));
            if($events->count() >= $eventChunk->get('eventcount')) {
                break;
            }
            $page++;
        }
        return $events;
    }
}
