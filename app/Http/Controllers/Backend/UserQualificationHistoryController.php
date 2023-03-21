<?php

namespace App\Http\Controllers\Backend;

use App\Exports\DefaultExport;
use App\Http\Controllers\Controller;
use App\Models\App;
use App\Services\PermissionEngine;
use App\Services\UserEngine;
use App\Stats\Live\UserCourseStats;
use App\Stats\Live\UserTestStats;
use App\Traits\PersonalData;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class UserQualificationHistoryController extends Controller
{
    use PersonalData;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:,users-personaldata');
        $this->personalDataRightsMiddleware('users');
    }

    /**
     * Export qualification history for the user.
     */
    public function export($userId, UserEngine $userEngine, PermissionEngine $permissionEngine) {
        if (!$this->showPersonalData) {
            app()->abort(403);
        }
        $user = $userEngine->getUser($userId);
        $adminUser = Auth::user();
        $app = App::find(appId());
        $filename = 'qualification-history-'.Str::slug($user->username, '-').'-'.Carbon::now()->format('d.m.Y-H:i').'.xlsx';

        $qualificationHistory = $userEngine->getQualificationHistory($user, $adminUser);
        $qualificationHistory->transform(function ($item) {
            $item['status'] = $this->getStatusLabel($item['status']);
            return $item;
        });

        $hasTestHistory = ($this->appSettings->getValue('module_tests') && ($adminUser->hasRight('tests-view') || $adminUser->hasRight('tests-edit')));
        if ($hasTestHistory) {
            $qualificationHistoryForTests = $qualificationHistory->filter(function ($entry) {
                return $entry['type'] == 'test';
            })->values();
            app(UserTestStats::class)->attach(collect([$user]));
        } else {
            $qualificationHistoryForTests = collect([]);
        }

        $hasCourseHistory = ($this->appSettings->getValue('module_courses') && ($adminUser->hasRight('courses-view') || $adminUser->hasRight('courses-edit')));
        if ($hasCourseHistory) {
            $qualificationHistoryForCourses = $qualificationHistory->filter(function ($entry) {
                return $entry['type'] == 'course';
            })->values();
            app(UserCourseStats::class)->attach(collect([$user]));
        } else {
            $qualificationHistoryForCourses = collect([]);
        }

        $rowCount = max($qualificationHistoryForTests->count(), $qualificationHistoryForCourses->count(), $user->tags->count());

        $tagGroups = $permissionEngine->getAvailableTagGroups($this->appSettings->getAppId(), $adminUser);

        $data = [
            'user' => $user,
            'tagGroups' => $tagGroups,
            'metaFields' => $app->getUserMetaDataFields($this->showPersonalData),
            'qualificationHistoryForTests' => $qualificationHistoryForTests,
            'qualificationHistoryForCourses' => $qualificationHistoryForCourses,
            'totalDuration' => $qualificationHistoryForTests->sum('duration') + $qualificationHistoryForCourses->sum('duration'),
            'rowCount' => $rowCount,
            'adminUser' => $adminUser,
            'appSettings' => $this->appSettings,
            'showEmails' => $this->showEmails,
            'hasCourseHistory' => $hasCourseHistory,
            'hasTestHistory' => $hasTestHistory,
        ];

        return Excel::download(new DefaultExport($data, 'users.csv.qualification-history'), $filename);

    }

    private function getStatusLabel($passed) {
        if($passed) {
            return 'passed';
        }
        elseif($passed === 0) {
            return 'failed';
        }
        return 'in_progress';
    }

}
