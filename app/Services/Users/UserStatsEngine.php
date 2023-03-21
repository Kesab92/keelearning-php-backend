<?php

namespace App\Services\Users;

use App\Models\App;
use App\Models\User;
use App\Services\AppSettings;
use App\Services\PermissionEngine;
use App\Stats\Live\LastOnline;
use App\Stats\Live\UserCourseStats;
use App\Stats\Live\UserGameStats;
use App\Stats\Live\UserPowerlearningStats;
use App\Stats\Live\UserTestStats;

class UserStatsEngine {
    /**
     * @var PermissionEngine
     */
    private PermissionEngine $permissionEngine;

    public function __construct(PermissionEngine $permissionEngine) {
        $this->permissionEngine = $permissionEngine;
    }

    public function getUserStats(
        array $tags,
        AppSettings $settings,
        ?int $page = null,
        ?int $perPage = 50,
        string $sortBy = 'id',
        bool $sortDescending = false,
        User $adminUser = null,
        bool $isExport = false,
        bool $showPersonalData = false,
        bool $showEmails = false
    ) {
        $users = User::where('app_id', $settings->getAppId())
            ->where('is_dummy', false)
            ->where('is_api_user', false)
            ->whereNull('deleted_at')
            ->with(['metafields', 'tags']);
        $metafields = App::findOrFail($settings->getAppId())->getUserMetaDataFields($showPersonalData);

        $showVouchers = $isExport && $settings->getValue('module_vouchers') && (!$adminUser || $adminUser->hasRight('vouchers-edit'));
        if ($showVouchers) {
            $users = $users->with('voucherCodes.voucher');
        }

        $users = $users->get();


        if($adminUser !== null) {
            $users = $this->permissionEngine->filterPlayerStatsByTag($adminUser, $users);
        }

        $users = $users
            ->filter(function (User $user) use ($tags) {
                if ($tags && count($tags)) {
                    $selectWithoutTag = in_array(-1, $tags);
                    $tags = array_filter($tags, function ($tag) {
                        return $tag !== '-1';
                    });
                    if ($selectWithoutTag && !$user->tags->count()) {
                        return true;
                    }
                    if (count($tags)) {
                        // Only select users which have at least one of the selected tags
                        return $user->tags->pluck('id')->intersect($tags)->count() > 0;
                    }
                    return false;
                }
                return true;
            })
            ->sortBy($sortBy,SORT_REGULAR, $sortDescending);

        $userCount = $users->count();
        if($page !== null) {
            $users = $users->forPage($page, $perPage);
        }
        $users = $users->values()
            ->transform(function (User $user) use ($showVouchers, $metafields, $showEmails, $showPersonalData) {
                $data = [
                    'id' => $user->id,
                    'app_id' => $user->app_id,
                    'meta' => [],
                    'tags' => $user->tags,
                ];

                foreach ($metafields as $metafield => $metavalue) {
                    $data['meta'][$metafield] = $user->getMeta($metafield) ?? '';
                }

                if ($showPersonalData) {
                    $data['username'] = $user->username;
                    $data['firstname'] = $user->firstname;
                    $data['lastname'] = $user->lastname;

                    if ($showEmails) {
                        $data['email'] = $user->email;
                    }
                }
                if ($showVouchers) {
                    $data['vouchers'] = $user->voucherCodes->map(function($vC) {
                        return [
                            'name' => $vC->voucher->name,
                            'code' => $vC->code,
                        ];
                    });
                }
                return $data;
            });
        $headers = [
            [
                'text' => 'ID',
                'value' => 'id',
                'sortable' => true,
            ],
        ];
        if ($showPersonalData) {
            $headers[] = [
                'text' => 'Benutzer',
                'value' => 'username',
                'sortable' => true,
            ];
            $headers[] = [
                'text' => 'Vorname',
                'value' => 'firstname',
                'sortable' => true,
            ];
            $headers[] = [
                'text' => 'Nachname',
                'value' => 'lastname',
                'sortable' => true,
            ];
        }
        foreach (App::find($settings->getAppId())->getUserMetaDataFields($showPersonalData) as $metaKey => $metaConfig) {
            $headers[] = [
                'text' => $metaConfig['label'],
                'value' => 'meta.' . $metaKey,
                'sortable' => false,
            ];
        }
        $headers[] = [
            'text' => 'TAGs',
            'value' => 'tags',
            'sortable' => false,
        ];
        if ($showVouchers) {
            $headers[] = [
                'text' => 'Eingelöste Voucher',
                'value' => 'vouchers',
                'sortable' => false,
            ];
        }
        $headers[] = [
            'text' => 'Zuletzt online',
            'value' => 'last_online',
            'sortable' => false,
        ];
        \app(LastOnline::class)->attach($users);

        if ($settings->getValue('module_quiz')) {
            $headers[] = [
                'text' => 'Spiele gesamt',
                'value' => 'games',
                'sortable' => false,
            ];
            $headers[] = [
                'text' => 'Spiele gegen Menschen',
                'value' => 'human_games',
                'sortable' => false,
            ];
            $headers[] = [
                'text' => 'Spiele gewonnen gegen Menschen',
                'value' => 'human_win_percentage',
                'sortable' => false,
            ];
            $headers[] = [
                'text' => 'Letztes Spiel',
                'value' => 'last_game',
                'sortable' => false,
            ];
            \app(UserGameStats::class)->attach($users);
        }

        if ($settings->getValue('module_powerlearning')) {
            $headers[] = [
                'text' => 'Fragen mind. einmal gelernt',
                'value' => 'learned_questions',
                'sortable' => false,
            ];
            \app(UserPowerlearningStats::class)->attach($users);
        }

        if ($settings->getValue('module_tests') && (!$adminUser || $adminUser->hasRight('tests-stats'))) {
            $headers[] = [
                'text' => 'Tests bestanden',
                'value' => 'passed_tests',
                'sortable' => false,
            ];
            \app(UserTestStats::class)->attach($users);
        }

        if ($settings->getValue('module_courses') && (!$adminUser || $adminUser->hasRight('courses-stats'))) {
            $headers[] = [
                'text' => 'Kurse bestanden',
                'value' => 'passed_courses',
                'sortable' => false,
            ];
            \app(UserCourseStats::class)->attach($users);
        }

        return [
            'users' => $users,
            'userCount' => $userCount,
            'headers' => $headers,
        ];
    }
}
