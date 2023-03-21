<?php

namespace App\Services\Users;

use App\Models\App;
use App\Models\Courses\Course;
use App\Models\Test;
use App\Models\User;
use App\Services\AppSettings;
use App\Services\PermissionEngine;
use App\Services\TestEngine;
use Carbon\Carbon;
use Config;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersStatsExport implements WithMapping, FromCollection, WithHeadings, ShouldAutoSize
{
    /**
     * @var Collection
     */
    private $stats;
    /**
     * @var array
     */
    private $headers;
    /**
     * @var AppSettings
     */
    private AppSettings $settings;
    /**
     * @var User|null
     */
    private ?User $adminUser;
    /**
     * @var \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Course[]
     */
    private $courses;
    /**
     * @var \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Test[]
     */
    private $tests;
    /**
     * @var \App\Models\Tag[]|\Illuminate\Database\Eloquent\Collection
     */
    private $tags;
    /**
     * @var \App\Models\TagGroup[]|\Illuminate\Database\Eloquent\Collection
     */
    private $tagGroups;
    /**
     * @var boolean
     */
    private $isReporting;
    /**
     * @var boolean
     */
    private $showPersonalData;
    /**
     * @var boolean
     */
    private $showEmails;

    public function __construct(AppSettings $settings, $tags = [], User $adminUser = null, bool $isReporting = false, bool $showPersonalData = false, bool $showEmails = false) {
        $userStatsEngine = app(UserStatsEngine::class);

        $data = $userStatsEngine->getUserStats($tags, $settings, null, null, 'id', false, $adminUser, true, $showPersonalData, $showEmails);

        $this->stats = $data['users'];
        $this->headers = $data['headers'];
        $this->settings = $settings;
        $this->adminUser = $adminUser;
        $this->isReporting = $isReporting;
        $this->showPersonalData = $showPersonalData;
        $this->showEmails = $showEmails;

        $this->collectEntityData();
    }

    public function headings(): array
    {
        $adminUser = Auth::user();
        $headings = [];
        foreach($this->headers as $header) {
            $text = $header['text'];
            switch ($header['value']) {
                case 'username':
                    $headings[] = $text;
                    if($this->showEmails) {
                        $headings[] = 'E-Mail';
                    }
                    break;
                default:
                    $headings[] = $text;
            }
        }

        if (($adminUser && $adminUser->hasRight('tests-stats')) || !$adminUser) {
            foreach ($this->tests as $test) {
                $headings[] = 'Test: ' . $test->name;
            }
        }

        if (($adminUser && $adminUser->hasRight('courses-stats')) || !$adminUser) {
            foreach ($this->courses as $course) {
                $headings[] = 'Kurs: ' . $course->title;
            }
        }

        if($this->settings->getAppId() !== App::ID_GREEN_CARE) {
            foreach($this->tagGroups as $tagGroup) {
                $headings[] = 'TAG Gruppe: ' . $tagGroup->name;
            }
            foreach($this->tags as $tag) {
                $headings[] = 'TAG: ' . $tag->label;
            }
        }

        return $headings;
    }

    public function map($user): array
    {
        $adminUser = Auth::user();

        $data = [];
        foreach($this->headers as $header) {
            $value = Arr::get($user, $header['value']);
            switch ($header['value']) {
                case 'username':
                    $data[] = $value;
                    if($this->showEmails) {
                        $data[] = $user['email'];
                    }
                    break;
                case 'tags':
                    $data[] = collect($value)->pluck('label')->implode(', ');
                    break;
                case 'vouchers':
                    $data[] = collect($value)->map(function($voucher) {
                        return $voucher['name'] . ': ' . $voucher['code'];
                    })->implode("\n");
                    break;
                case 'last_game':
                    $data[] = (new Carbon($value))->format('Y-m-d');
                    break;
                case 'passed_tests':
                case 'passed_courses':
                    $data[] = count($value);
                    break;
                default:
                    $data[] = $value;
            }
        }

        if (($adminUser && $adminUser->hasRight('tests-stats')) || !$adminUser) {
            foreach ($this->tests as $test) {
                if ($user['passed_tests']->contains($test->id)) {
                    $data[] = 'passed';
                } elseif ($user['failed_tests']->contains($test->id)) {
                    $data[] = 'failed';
                } elseif ($user['attempted_tests']->contains($test->id)) {
                    $data[] = 'in_progress';
                } else {
                    $data[] = '';
                }
            }
        }

        if (($adminUser && $adminUser->hasRight('courses-stats')) || !$adminUser) {
            foreach ($this->courses as $course) {
                if ($user['passed_courses']->contains($course->id)) {
                    $data[] = 'passed';
                } elseif ($user['failed_courses']->contains($course->id)) {
                    $data[] = 'failed';
                } elseif ($user['attempted_courses']->contains($course->id)) {
                    $data[] = 'in_progress';
                } else {
                    $data[] = '';
                }
            }
        }

        if($this->settings->getAppId() !== App::ID_GREEN_CARE) {
            foreach ($this->tagGroups as $tagGroup) {
                $data[] = $user['tags']->where('tag_group_id', $tagGroup->id)->pluck('label')->implode(', ');
            }
            foreach ($this->tags as $tag) {
                if ($user['tags']->contains('id', $tag->id)) {
                    $data[] = 'x';
                } else {
                    $data[] = '';
                }
            }
        }

        return $data;
    }

    public function collection()
    {
        return $this->stats;
    }

    /**
     * This method gets data about the tests, courses and TAGs which we need when generating the content
     */
    private function collectEntityData()
    {
        Config::set('app.force_language', defaultAppLanguage($this->settings->getAppId()));

        if($this->adminUser && !$this->adminUser->hasRight('tests-stats')) {
            $this->tests = collect([]);
        } else {
            $adminUser = null;
            if(!$this->isReporting) {
                $adminUser = $this->adminUser;
            }

            $testEngine = app(TestEngine::class);
            $this->tests = $testEngine
                ->testsFilterQuery($this->settings->getAppId(), $adminUser, null, null, null, null, null)
                ->get();

            $this->tests->load('translationRelation')
                ->sortBy('name', SORT_FLAG_CASE | SORT_NATURAL);
        }

        if($this->adminUser && !$this->adminUser->hasRight('courses-stats')) {
            $this->courses = collect([]);
        } else {
            $this->courses = Course::where('app_id', $this->settings->getAppId())
                ->where('visible', 1)
                ->get();

            $this->courses->load('translationRelation')
                ->sortBy('title', SORT_FLAG_CASE | SORT_NATURAL);
        }

        $permissionEngine = app(PermissionEngine::class);
        $this->tags = $permissionEngine->getAvailableTags($this->settings->getAppId(), $this->adminUser);
        $this->tagGroups = $permissionEngine->getAvailableTagGroups($this->settings->getAppId(), $this->adminUser);
    }
}
