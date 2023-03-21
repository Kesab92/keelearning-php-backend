<?php

namespace App\Services\Reports;

use App\Models\App;
use App\Models\Courses\Course;
use App\Models\Courses\CourseParticipation;
use App\Models\Test;
use App\Models\TestSubmission;
use App\Services\AppSettings;
use App\Models\User;
use App\Services\Courses\CoursesEngine;
use App\Services\PermissionEngine;
use App\Services\TestEngine;
use App\Services\Users\UserStatsEngine;
use App\Stats\Live\LastOnline;
use App\Traits\PersonalData;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class UserReport extends Report implements ReportInterface
{
    use PersonalData;

    private Collection $tagRights;
    private Collection $availableTags;
    private Collection $availableTagGroups;
    private Collection $availableCourses;
    private Collection $availableTests;
    private array $availableMetaFields;
    private TestEngine $testEngine;

    const COLUMNS = [
        'id' => [
            'name' => 'ID',
            'necessarySettings' => [
                'user_id',
            ],
        ],
        'username' => [
            'name' => 'Benutzer',
            'necessarySettings' => [
                'username',
            ],
        ],
        'email' => [
            'name' => 'E-Mail',
            'necessarySettings' => [
                'email',
            ],
        ],
        'metafields' => [
            'name' => '',
            'necessarySettings' => [
                'metafields',
            ],
        ],
        'firstname' => [
            'name' => 'Vorname',
            'necessarySettings' => [
                'user_names',
            ],
        ],
        'lastname' => [
            'name' => 'Nachname',
            'necessarySettings' => [
                'user_names',
            ],
        ],
        'tags' => [
            'name' => 'TAGs',
            'necessarySettings' => [
                'tags',
            ],
        ],
        'sum_total_duration' => [
            'name' => 'Summe Dauer ',
            'necessarySettings' => [
                'total_duration',
            ],
        ],
        'vouchers' => [
            'name' => 'Eingelöste Voucher',
            'necessarySettings' => [
                'redeemed_vouchers',
            ],
        ],
        'last_online' => [
            'name' => 'Zuletzt online',
            'necessarySettings' => [
                'last_online',
            ],
        ],
        'total_games' => [
            'name' => 'Spiele gesamt',
            'necessarySettings' => [
                'quiz_battle',
                'total_games',
            ],
        ],
        'human_games' => [
            'name' => 'Spiele gegen Menschen',
            'necessarySettings' => [
                'quiz_battle',
                'human_games',
            ],
        ],
        'human_wins' => [
            'name' => 'Spiele gewonnen gegen Menschen',
            'necessarySettings' => [
                'quiz_battle',
                'human_wins',
            ],
        ],
        'last_game' => [
            'name' => 'Letztes Spiel',
            'necessarySettings' => [
                'quiz_battle',
                'last_game',
            ],
        ],
        'learned_questions' => [
            'name' => 'Fragen mind. einmal gelernt',
            'necessarySettings' => [
                'powerlearning',
            ],
        ],
        'passed_tests' => [
            'name' => 'Tests bestanden',
            'necessarySettings' => [
                'tests',
            ],
        ],
        'passed_courses' => [
            'name' => 'Kurse bestanden',
            'necessarySettings' => [
                'courses',
            ],
        ],
        'tests' => [
            'name' => 'Test: ',
            'necessarySettings' => [
                'tests',
            ],
        ],
        'test_total_duration' => [
            'name' => 'Dauer Test: ',
            'necessarySettings' => [
                'tests',
                'total_duration',
            ],
        ],
        'test_finished_at' => [
            'name' => 'Abschlussdatum Test: ',
            'necessarySettings' => [
                'tests',
                'finished_at',
            ],
        ],
        'courses' => [
            'name' => 'Kurs: ',
            'necessarySettings' => [
                'courses',
            ],
        ],
        'course_total_duration' => [
            'name' => 'Dauer Kurs: ',
            'necessarySettings' => [
                'courses',
                'total_duration',
            ],
        ],
        'course_finished_at' => [
            'name' => 'Abschlussdatum Kurs: ',
            'necessarySettings' => [
                'courses',
                'finished_at',
            ],
        ],
        'tag_groups' => [
            'name' => 'TAG Gruppe: ',
            'necessarySettings' => [
                'tag_groups',
            ],
        ],
        'column_per_tag' => [
            'name' => 'TAG: ',
            'necessarySettings' => [
                'column_per_tag',
            ],
        ],
    ];

    public function __construct(User $user, Collection $settings, AppSettings $appSettings, Collection $tagRights)
    {
        parent::__construct($user, $settings);

        $this->tagRights = $tagRights;

        $this->checkPersonalDataRights('users', $this->user);

        if(!$this->hasPermissions()) {
            abort(403);
        }

        $this->availableTags = collect([]);
        $this->availableTagGroups = collect([]);
        $this->availableCourses = collect([]);
        $this->availableTests = collect([]);

        $this->availableMetaFields = App::findOrFail($this->appSettings->getAppId())->getUserMetaDataFields( $this->showPersonalData);

        $permissionEngine = app(PermissionEngine::class);
        $this->testEngine = app(TestEngine::class);

        if($this->hasNecessarySettings(self::COLUMNS['column_per_tag']['necessarySettings']) && $this->appSettings->getApp()->containsTagsInReport()) {
            $this->availableTags = $permissionEngine->getAvailableTags($this->appSettings->getAppId(), $this->user);
        }

        if($this->hasNecessarySettings(self::COLUMNS['tag_groups']['necessarySettings']) && $this->appSettings->getApp()->containsTagsInReport()) {
            $this->availableTagGroups = $permissionEngine->getAvailableTagGroups($this->appSettings->getAppId(), $this->user);
        }

        if($this->hasNecessarySettings(self::COLUMNS['courses']['necessarySettings'])) {
            $this->availableCourses = Course::tagRights($this->user)->where('app_id', $this->appSettings->getAppId())
                ->where('visible', 1)
                ->get();

            $this->availableCourses->load('translationRelation')
                ->sortBy('title', SORT_FLAG_CASE | SORT_NATURAL);
        }

        if($this->hasNecessarySettings(self::COLUMNS['tests']['necessarySettings'])) {
            $this->availableTests = $this->testEngine
                ->testsFilterQuery($this->appSettings->getAppId(), $this->user)
                ->get();

            $this->availableTests->load('translationRelation')
                ->sortBy('name', SORT_FLAG_CASE | SORT_NATURAL);
        }
    }

    protected function hasPermissions(): bool
    {
        $allowedTagRights = $this->user->tagRightsRelation->pluck('id')->intersect($this->tagRights);

        if(!$this->user->hasRight('users-stats')) {
            return false;
        }
        if(!$this->user->isFullAdmin() && $this->tagRights->count() !== $allowedTagRights->count()) {
            return false;
        }
        if($this->settings->contains('username') && !$this->showPersonalData) {
            return false;
        }
        if($this->settings->contains('user_names') && !$this->showPersonalData) {
            return false;
        }
        if($this->settings->contains('email') && !$this->showEmails) {
            return false;
        }
        if($this->settings->contains('redeemed_vouchers') && (!$this->appSettings->getValue('module_vouchers') || !$this->user->hasRight('vouchers-edit'))) {
            return false;
        }
        if($this->settings->contains('courses') && (!$this->appSettings->getValue('module_courses') || !$this->user->hasRight('courses-stats'))) {
            return false;
        }
        if($this->settings->contains('tests') && (!$this->appSettings->getValue('module_tests') || !$this->user->hasRight('tests-stats'))) {
            return false;
        }
        if($this->settings->contains('quiz_battle') && !$this->appSettings->getValue('module_quiz')) {
            return false;
        }
        if($this->settings->contains('powerlearning') && !$this->appSettings->getValue('module_powerlearning')) {
            return false;
        }

        return true;
    }

    public function prepareReport(): void
    {
        $userStatsEngine = app(UserStatsEngine::class);

        $data = $userStatsEngine->getUserStats($this->tagRights->toArray(), $this->appSettings, null, null, 'id', false, $this->user, true, $this->showPersonalData, $this->showEmails);

        $this->prepareHeaders();
        $this->prepareData($data['users']);
    }

    private function prepareHeaders():void {
        foreach (self::COLUMNS as $key => $column) {
            if (!$this->hasNecessarySettings($column['necessarySettings'])) {
                continue;
            }

            switch ($key) {
                case 'metafields':
                    foreach (App::find($this->appSettings->getAppId())->getUserMetaDataFields($this->showPersonalData) as $metaKey => $metaConfig) {
                        $this->headers['meta.' . $metaKey] = $metaConfig['label'];
                    }
                    break;
                case 'tests':
                    foreach($this->availableTests as $test) {
                        $this->headers['test_' . $test->id] = $column['name'] . $test->name;

                        if ($this->hasNecessarySettings(self::COLUMNS['test_total_duration']['necessarySettings'])) {
                            $this->headers['test_total_duration_' . $test->id] = self::COLUMNS['test_total_duration']['name'] . $test->name;
                        }
                        if ($this->hasNecessarySettings(self::COLUMNS['test_finished_at']['necessarySettings'])) {
                            $this->headers['test_finished_at_' . $test->id] = self::COLUMNS['test_finished_at']['name'] . $test->name;
                        }
                    }
                    break;
                case 'courses':
                    foreach($this->availableCourses as $course) {
                        $this->headers['course_' . $course->id] = $column['name'] . $course->title;

                        if ($this->hasNecessarySettings(self::COLUMNS['course_total_duration']['necessarySettings'])) {
                            $this->headers['course_total_duration_' . $course->id] = self::COLUMNS['course_total_duration']['name'] . $course->title;
                        }
                        if ($this->hasNecessarySettings(self::COLUMNS['course_finished_at']['necessarySettings'])) {
                            $this->headers['course_finished_at_' . $course->id] = self::COLUMNS['course_finished_at']['name'] . $course->title;
                        }
                    }
                    break;
                case 'tag_groups':
                    foreach($this->availableTagGroups as $tagGroup) {
                        $this->headers['tag_group_' . $tagGroup->id] = $column['name'] . $tagGroup->name;
                    }
                    break;
                case 'column_per_tag':
                    foreach($this->availableTags as $tag) {
                        $this->headers['tag_' . $tag->id] = $column['name'] . $tag->label;
                    }
                    break;
                case 'sum_total_duration':
                    $this->headers[$key] = $column['name'] . '(' . Carbon::now()->year . ')';
                    break;
                case 'test_finished_at':
                case 'test_total_duration':
                case 'course_total_duration':
                case 'course_finished_at':
                    break;
                default:
                    $this->headers[$key] = $column['name'];
                    break;
            }
        }
    }

    private function prepareData($usersFromStats):void {
        $coursesEngine = app(CoursesEngine::class);
        if($this->hasNecessarySettings(self::COLUMNS['last_online']['necessarySettings'])) {
            \app(LastOnline::class)->attach($usersFromStats);
        }

        $users = null;
        if($this->hasNecessarySettings(self::COLUMNS['course_total_duration']['necessarySettings']) || $this->hasNecessarySettings(self::COLUMNS['course_finished_at']['necessarySettings'])) {
            $users = User
                ::whereIn('id', $usersFromStats->pluck('id'))
                ->with('tags')
                ->groupBy('id')
                ->get();
        }

        $testIdsOfTestSubmissionsInCurrentYear = null;
        $testDurationsInCurrentYear = null;
        $courseIdsOfCourseParticipationsInCurrentYear = null;
        $coursesInCurrentYear = null;
        $lastFinishedSubmissions = null;
        $userCourseDurations = null;
        $courseParticipations = null;

        if ($this->hasNecessarySettings(self::COLUMNS['test_total_duration']['necessarySettings'])) {
            $testIdsOfTestSubmissionsInCurrentYear = $this->getTestIdsOfTestSubmissionInCurrentYear($usersFromStats);
            $testDurationsInCurrentYear = $this->getTestDurations($usersFromStats, $testIdsOfTestSubmissionsInCurrentYear);
        }
        if($this->hasNecessarySettings(self::COLUMNS['course_total_duration']['necessarySettings'])) {
            $courseIdsOfCourseParticipationsInCurrentYear = $this->getCourseIdsOfCourseParticipationsInCurrentYear($usersFromStats);
            $coursesInCurrentYear = $this->getCourses($usersFromStats, $courseIdsOfCourseParticipationsInCurrentYear);

            $userCourseDurations = $coursesEngine->getUsersCourseDurations($this->availableCourses, $users)->keyBy('user_id');
        }
        if ($this->hasNecessarySettings(self::COLUMNS['course_finished_at']['necessarySettings'])) {
            $courseParticipations = $coursesEngine->getUserFinishedCourseParticipations($this->availableCourses, $users)->groupBy('user_id');
        }
        if ($this->hasNecessarySettings(self::COLUMNS['test_finished_at']['necessarySettings'])) {
            $lastFinishedSubmissions = $this->testEngine->getLastFinishedTestSubmissions($this->availableTests->pluck('id'), $usersFromStats->pluck('id'))->groupBy('user_id');
        }

        $availableCourseIds = $this->availableCourses->pluck('id');
        $availableTestIds = $this->availableTests->pluck('id');
        $availableTagIds = $this->availableTags->pluck('id');
        $availableTagGroupIds = $this->availableTagGroups->pluck('id');
        $availableTestDurations = $this->availableTests->pluck('minutes', 'id');

        $this->data = $usersFromStats->map(function ($userStatsData) use (
            $availableTestDurations,
            $availableTagGroupIds,
            $availableTagIds, $availableTestIds,
            $availableCourseIds,
            $courseParticipations,
            $userCourseDurations,
            $lastFinishedSubmissions,
            $testIdsOfTestSubmissionsInCurrentYear,
            $testDurationsInCurrentYear,
            $courseIdsOfCourseParticipationsInCurrentYear,
            $coursesInCurrentYear,
            $coursesEngine,
            $users
        ) {
            $userData = [];
            $courseStatuses = $this->getCourseStatuses($userStatsData, $availableCourseIds);
            $testStatuses = $this->getTestStatuses($userStatsData, $availableTestIds);
            $userTags = $userStatsData['tags']->keyBy('id');

            foreach (self::COLUMNS as $key => $column) {
                if (!$this->hasNecessarySettings($column['necessarySettings'])) {
                    continue;
                }
                switch ($key) {
                    case 'metafields':
                        foreach ($this->availableMetaFields as $metafield => $metavalue) {
                            $userData['meta.' . $metafield] = $userStatsData['meta'][$metafield];
                        }
                        break;
                    case 'tests':
                        foreach ($availableTestDurations as $testId => $testDuration) {
                            $userData['test_' . $testId] = $testStatuses->get($testId);

                            if ($this->hasNecessarySettings(self::COLUMNS['test_total_duration']['necessarySettings'])) {
                                $userData['test_total_duration_' . $testId] = !empty($testDuration) ? $testDuration : 0;
                            }
                            if ($this->hasNecessarySettings(self::COLUMNS['test_finished_at']['necessarySettings'])) {
                                $lastFinishedSubmission = null;
                                $finishedAt = '';
                                if($lastFinishedSubmissions->has($userStatsData['id'])) {
                                    $lastFinishedSubmission = $lastFinishedSubmissions->get($userStatsData['id'])->where('test_id', $testId)->first();
                                }
                                if($lastFinishedSubmission) {
                                    $finishedAt = $lastFinishedSubmission->updated_at->format('d.m.Y');
                                }
                                $userData['test_finished_at_' . $testId] = $finishedAt;
                            }
                        }
                        break;
                    case 'courses':
                        $courseDurations = null;
                        if ($this->hasNecessarySettings(self::COLUMNS['course_total_duration']['necessarySettings'])) {
                            $courseDurations = $userCourseDurations->get($userStatsData['id']);
                        }
                        foreach ($availableCourseIds as $courseId) {
                            $userData['course_' . $courseId] = $courseStatuses->get($courseId);

                            if ($this->hasNecessarySettings(self::COLUMNS['course_total_duration']['necessarySettings'])) {
                                $courseTotalDuration = null;

                                if($courseDurations && isset($courseDurations['durations'][$courseId])) {
                                    $courseTotalDuration = $courseDurations['durations'][$courseId];
                                }

                                $userData['course_total_duration_' . $courseId] = $courseTotalDuration;
                            }
                            if ($this->hasNecessarySettings(self::COLUMNS['course_finished_at']['necessarySettings'])) {
                                $finishedAt = '';
                                $courseParticipation = null;

                                if($courseParticipations->has($userStatsData['id'])) {
                                    $courseParticipation = $courseParticipations
                                        ->get($userStatsData['id'])
                                        ->where('course_id', $courseId)
                                        ->whereNotNull('finished_at')
                                        ->first();
                                }

                                if($courseParticipation) {
                                    $finishedAt = $courseParticipation->finished_at->format('d.m.Y');
                                }

                                $userData['course_finished_at_' . $courseId] = $finishedAt;
                            }
                        }
                        break;
                    case 'tag_groups':
                        foreach ($availableTagGroupIds as $tagGroupId) {
                            $userData['tag_group_' . $tagGroupId] = $userStatsData['tags']->where('tag_group_id', $tagGroupId)->pluck('label')->implode(', ');
                        }

                        break;
                    case 'column_per_tag':
                        foreach ($availableTagIds as $tagId) {
                            if ($userTags->has($tagId)) {
                                $userData['tag_' . $tagId] = 'X';
                            } else {
                                $userData['tag_' . $tagId] = '';
                            }
                        }
                        break;
                    case 'tags':
                        if (isset($userStatsData['tags'])) {
                            $userData['tags'] = $userStatsData['tags']->pluck('label')->join(', ');
                        } else {
                            $userData['tags'] = '';
                        }
                        break;
                    case 'vouchers':
                        $userData['vouchers'] = collect($userStatsData['vouchers'])->map(function ($voucher) {
                            return $voucher['name'] . ': ' . $voucher['code'];
                        })->implode("\n");
                        break;
                    case 'total_games':
                        $userData['total_games'] = $userStatsData['games'];
                        break;
                    case 'human_wins':
                        $userData['human_wins'] = $userStatsData['human_win_percentage'] * 100 . '%';
                        break;
                    case 'passed_tests':
                        $userData['passed_tests'] = $userStatsData['passed_tests']->count();
                        break;
                    case 'passed_courses':
                        $userData['passed_courses'] = $userStatsData['passed_courses']->count();
                        break;
                    case 'sum_total_duration':
                        $sumTestTotalDuration = 0;
                        $sumCourseTotalDuration = 0;

                        if ($this->hasNecessarySettings(self::COLUMNS['test_total_duration']['necessarySettings'])) {
                            $testIdsInCurrentYear = $testIdsOfTestSubmissionsInCurrentYear->get($userStatsData['id']);

                            if($testIdsInCurrentYear) {
                                $userTestDurationsInCurrentYear = $testDurationsInCurrentYear->filter(function($testDuration, $testId) use ($testIdsInCurrentYear) {
                                    return $testIdsInCurrentYear->contains($testId);
                                });

                                $sumTestTotalDuration = $userTestDurationsInCurrentYear->sum(function ($testDuration) {
                                    return $testDuration;
                                });
                            }
                        }

                        if ($this->hasNecessarySettings(self::COLUMNS['course_total_duration']['necessarySettings'])) {
                            $courseIdsInCurrentYear = $courseIdsOfCourseParticipationsInCurrentYear->get($userStatsData['id']);

                            if($courseIdsInCurrentYear) {
                                $userCoursesInCurrentYear = $coursesInCurrentYear->whereIn('id', $courseIdsInCurrentYear);
                                $coursesEngine->attachCourseDurations($userCoursesInCurrentYear, $users->firstWhere('id', $userStatsData['id']));
                                $courseTotalDurations = $userCoursesInCurrentYear->pluck('total_duration');

                                $sumCourseTotalDuration = $courseTotalDurations->sum();
                            }
                        }

                        $userData['sum_total_duration'] = $sumCourseTotalDuration + $sumTestTotalDuration;
                        break;
                    case 'test_total_duration':
                    case 'test_finished_at':
                    case 'course_total_duration':
                    case 'course_finished_at':
                        break;
                    default:
                        $userData[$key] = $userStatsData[$key];
                        break;
                }
            }

            return collect($userData);
        })->toArray();
    }

    private function getCourseStatuses($user, Collection $courseIds): Collection
    {
        $passedCourses = isset($user['passed_courses']) ? $courseIds->intersect($user['passed_courses']) : collect([]);
        $failedCourses = isset($user['failed_courses']) ? $courseIds->intersect($user['failed_courses']) : collect([]);
        $attemptedCourses = isset($user['attempted_courses']) ? $courseIds->intersect($user['attempted_courses']) : collect([]);

        $courseStatuses = collect([]);

        foreach ($courseIds as $courseId) {
            $courseStatuses->put($courseId, '');
        }
        foreach ($attemptedCourses as $courseId) {
            $courseStatuses->put($courseId, 'in_progress');
        }
        foreach ($failedCourses as $courseId) {
            $courseStatuses->put($courseId, 'failed');
        }
        foreach ($passedCourses as $courseId) {
            $courseStatuses->put($courseId, 'passed');
        }

        return $courseStatuses;
    }

    private function getTestStatuses($user, Collection $testIds): Collection
    {
        $passedTests = isset($user['passed_tests']) ? $testIds->intersect($user['passed_tests']) : collect([]);
        $failedTests = isset($user['failed_tests']) ? $testIds->intersect($user['failed_tests']) : collect([]);
        $attemptedTests = isset($user['attempted_tests']) ? $testIds->intersect($user['attempted_tests']) : collect([]);

        $testStatuses = collect([]);

        foreach ($testIds as $testId) {
            $testStatuses->put($testId, '');
        }
        foreach ($attemptedTests as $testId) {
            $testStatuses->put($testId, 'in_progress');
        }
        foreach ($failedTests as $testId) {
            $testStatuses->put($testId, 'failed');
        }
        foreach ($passedTests as $testId) {
            $testStatuses->put($testId, 'passed');
        }


        return $testStatuses;
    }

    private function getTestIdsOfTestSubmissionInCurrentYear($usersFromStats): Collection
    {
        return TestSubmission
            ::whereIn('user_id', $usersFromStats->pluck('id'))
            ->whereYear('updated_at', Carbon::now()->year)
            ->whereNotNull('result')
            ->get()
            ->groupBy('user_id')
            ->map(fn($testSubmission) => $testSubmission->pluck('test_id')->unique()->values());
    }

    private function getTestDurations($usersFromStats, $testSubmissions)
    {
        $testIds = $usersFromStats->map(function ($userStatsData) use ($testSubmissions) {
            return $testSubmissions->get($userStatsData['id']) ?? [];
        })->flatten()->unique()->values();

        return Test
            ::ofApp($this->appSettings->getAppId())
            ->whereIn('id', $testIds)
            ->pluck('minutes', 'id');
    }

    private function getCourseIdsOfCourseParticipationsInCurrentYear($usersFromStats) {
        return CourseParticipation
            ::whereIn('user_id', $usersFromStats->pluck('id'))
            ->whereYear('updated_at', Carbon::now()->year)
            ->whereNotNull('passed')
            ->get()
            ->groupBy('user_id')
            ->map(fn($courseParticipation) => $courseParticipation->pluck('course_id')->unique()->values());
    }

    private function getCourses($usersFromStats, $courseParticipations) {
        $courseIds = $usersFromStats->map(function ($userStatsData) use ($courseParticipations) {
            return $courseParticipations->get($userStatsData['id']);
        })->flatten()->unique()->values();

        return Course
            ::where('app_id', $this->appSettings->getAppId())
            ->whereIn('id', $courseIds)
            ->get();
    }
}
