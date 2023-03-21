<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Models\Courses\Course;
use App\Services\StatsServerEngine;
use App\Traits\PersonalData;
use Auth;

class StatsController extends Controller
{
    use PersonalData;
    private StatsServerEngine $statsServerEngine;

    public function __construct()
    {
        parent::__construct();
        $this->statsServerEngine = app(StatsServerEngine::class);
    }

    public function dashboard()
    {
        $user = Auth::user();
        $this->checkPersonalDataRights('dashboard', $user);

        $meta = [];
        $stats = [
            'usercounts' => [],
        ];

        if (
            $user->hasRight('comments-personaldata')
            || $user->hasRight('users-edit')
            || $user->hasRight('users-view')
        ) {

            $stats['urgentnotifications'] = [
                'hasAccessComments' => $user->hasRight('comments-personaldata'),
                'hasAccessUsers' => $user->hasRight('users-edit') || $user->hasRight('users-view'),
                'showUserData' => $user->hasRight('dashboard-userdata'),
                'showPersonalData' => $this->showPersonalData,
                'showEmails' => false,
            ];
        }

        if (
            $user->hasRight('users-edit')
            || $user->hasRight('users-view')
            || $user->hasRight('users-stats')
        ) {
            $stats['activities'] = [
                'showUserData' => $user->hasRight('dashboard-userdata'),
                'showPersonalData' => $this->showPersonalData,
                'showEmails' => false,
            ];
        }

        if (
            $this->appSettings->isBackendVisible('courses')
            && ($user->hasRight('courses-edit') || $user->hasRight('courses-view'))
        ) {
            $stats['courses'] = [];
            $mandatoryCourses = Course::ofApp(appId())
                ->mandatory()
                ->currentAndPast()
                ->with('translationRelation')
                ->get()
                ->map(function ($course) {
                    return [
                        'id' => $course->id,
                        'title' => $course->title,
                        'available_until' => $course->duration_type == Course::DURATION_TYPE_FIXED ? $course->available_until : null,
                    ];
                });

            $stats['mandatorycontent'] = [
                'mandatory_course_ids' => $mandatoryCourses->pluck('id'),
                'show_individual_stats' => $user->hasRight('courses-stats'),
            ];

            if ($user->hasRight('courses-stats')) {
                $meta['mandatorycourses'] = $mandatoryCourses;
            }
        }
        if (
            $this->appSettings->isBackendVisible('courses')
            && ($user->hasRight('courses-edit') || $user->hasRight('courses-view'))
        ) {
            $stats['courses'] = [];
            $allCourses = Course::ofApp(appId())
                ->currentAndPast()
                ->with('translationRelation')
                ->get()
                ->map(function ($course) {
                    return [
                        'id' => $course->id,
                        'title' => $course->title,
                        'available_until' => $course->duration_type == Course::DURATION_TYPE_FIXED ? $course->available_until : null,
                    ];
                });

            $stats['courses'] = [
                'all_course_ids' => $allCourses->pluck('id'),
                'only' => ['participationsPerWeek'],
            ];
        }

        if (
            $this->appSettings->isBackendVisible('learningmaterials')
            && (
                $user->hasRight('learningmaterials-edit')
                || $user->hasRight('learningmaterials-stats')
            )
        ) {
            $stats['learningmaterials'] = [];
        }

        if (
            $this->appSettings->isBackendVisible('news')
            && $user->hasRight('news-edit')
        ) {
            $stats['news'] = [];
        }

        if (
            $this->appSettings->isBackendVisible('stats_quiz_challenge')
            && $this->appSettings->isBackendVisible('quiz')
            && (
                $user->hasRight('questions-edit')
                || $user->hasRight('questions-stats')
            )
        ) {
            $stats['quizgames'] = [];
        }

        return response()->json([
            'meta' => $meta,
            'stats' =>  $this->statsServerEngine->getStats($stats),
        ]);
    }
}
