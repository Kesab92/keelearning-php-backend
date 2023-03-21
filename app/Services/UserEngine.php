<?php

namespace App\Services;

use App\Http\APIError;
use App\Models\AnalyticsEvent;
use App\Models\App;
use App\Models\CategoryHider;
use App\Models\Courses\Course;
use App\Models\Courses\CourseContentAttempt;
use App\Models\Courses\CourseParticipation;
use App\Models\Game;
use App\Models\Tag;
use App\Models\Test;
use App\Models\User;
use App\Models\Voucher;
use App\Services\Courses\CoursesEngine;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class UserEngine
{
    /**
     * Returns a random opponent.
     *
     * @param User $user
     * @return APIError|array
     */
    public function findRandomOpponent(User $user)
    {
        $users = $this->findUsers(false, true, null);

        if ($users->count() == 0) {
            return new APIError(__('errors.no_opponent_available'));
        }

        // By default pick a random user
        $randomUser = $users->random();

        if (rand(1, 8) > 1) {
            // 7/8th chance to pick one of the most active users
            $activeUsers = $this->findActiveUsers($user->app_id);
            $availableActiveUsers = $users->filter(function ($user) use ($activeUsers) {
                return $activeUsers->has($user['id']);
            });
            $availableActiveUsers->transform(function ($user) use ($activeUsers) {
                $user['ngames'] = $activeUsers[$user['id']];

                return $user;
            });
            // Only pick an active user if there are at least 10 users in the pool of available users
            if ($availableActiveUsers->count() >= 10) {
                // Take the top 10 percent of users (at least 10)
                $n10Percent = max(0.1 * $users->count(), 10);
                // Take the top 10 percent of the available users
                $top10Users = $availableActiveUsers->sortByDesc('ngames')->take($n10Percent);
                // Choose a random user from the top10 available users
                $randomUser = $top10Users->random();
            }
        }

        if (isset($randomUser['ngames'])) {
            unset($randomUser['ngames']);
        }

        return $randomUser;
    }

    /**
     * Fetches 50 random players which are eligible to play against the current API user.
     *
     * @param bool $query
     * @param bool $checkLimits
     * @param int|null $amount Amount of users to return | null for all users
     *
     * @return User|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Query\Builder|static[]
     */
    public function findUsers($query = false, $checkLimits = false, $amount = 50)
    {
        $users = $this->getPossibleOpponents(user(), $query, $checkLimits);
        // Make sure we take random users, so our random opponent picking works when we have lots of users here
        $users = $users->shuffle();

        if ($amount === null) {
            return $users;
        }

        return $users->take($amount);
    }

    /**
     * Fetches all players which are eligible to play against the given user.
     *
     * @param User $user
     * @param bool $query
     * @param bool $checkLimits
     * @return User|Collection|Builder|static[]
     */
    public function getPossibleOpponents($user, $query = false, $checkLimits = false)
    {
        /** @var CategoryEngine $categoryEngine */
        $categoryEngine = app(CategoryEngine::class);
        $appProfile = $user->getAppProfile();
        $showMail = !$appProfile->getValue('hide_emails_frontend');
        $useRealName = $appProfile->getValue('use_real_name_as_displayname_frontend', false, true);
        // Check if the user has an exclusive tag (Which means they can only play with users having the same tag)
        $usersExclusiveTags = $user->tags->where('exclusive', true)->pluck('id')->toArray();

        $users = User::butNotThisOne($user->id)
            ->where('users.is_dummy', false)
            ->where('users.is_api_user', false)
            ->whereNull('users.deleted_at')
            ->where('users.active', 1)
            ->ofSameApp($user->app_id);

        // Filter by query
        if ($query) {
            $users
                ->where(function (Builder $q) use ($useRealName, $query, $showMail) {
                    $q->whereRaw('username LIKE ?', '%'.escapeLikeInput($query).'%');
                    if ($showMail) {
                        $q->orWhereRaw('email LIKE ?', escapeLikeInput($query));
                    }
                if ($useRealName) {
                    $q->orWhereRaw('CONCAT_WS(\' \', firstname, lastname) LIKE ?', '%'.escapeLikeInput($query).'%');
                }
            });
        }

        if ($usersExclusiveTags) {
            // Only select users with at least one shared exclusive TAG
            $users->whereHas('tags', function (Builder $query) use ($usersExclusiveTags) {
                $query->whereIn('tags.id', $usersExclusiveTags);
            });
        } else {
            // Exclude users with exclusive TAGs
            $exclusiveTagIDs = Tag::ofApp($user->app_id)
                ->where('exclusive', true)
                ->pluck('id');
            if ($exclusiveTagIDs->count()) {
                $users->whereDoesntHave('tags', function (Builder $query) use ($exclusiveTagIDs) {
                    $query->whereIn('tags.id', $exclusiveTagIDs);
                });
            }
        }

        // Only select users which can play in at least one of the user's categories
        $usersCategories = $user->getQuestionCategories(CategoryHider::SCOPE_QUIZ, false);
        $users->whereIn('users.id', $categoryEngine->usersWithAccessToCategories($usersCategories->pluck('id'), $user->app_id));

        if ($checkLimits && $maxGames = $user->app->maxConcurrentGames()) {
            // Exclude users which have more or equal to $maxGames active
            $users->whereRaw('
            users.id NOT IN (
                SELECT user_id FROM (
                    SELECT
                        count(*) as c,
                        if(games.player1_id = ' . $user->id . ', player2_id, player1_id) as user_id
                    FROM games
                    WHERE
                        status > 0
                        AND (
                            (player1_id = ' . $user->id . ')
                            OR (player2_id = ' . $user->id . ')
                        )
                    GROUP BY user_id
                    HAVING c >= ' . $maxGames . '
                ) as relevant
            )');
        }

        $users = $users->with(['app', 'tags'])
            ->select(['id', 'avatar', 'avatar_url', 'app_id', 'username', 'firstname', 'lastname', 'email'])
            ->get();

        return $users->map(function ($user) use ($showMail) {
            $avatar = $user->avatar_url;

            return [
                'avatar' => $avatar,
                'avatar_url' => $avatar,
                'email' => $showMail ? $user->email : null,
                'id' => $user->id,
                'username' => $user->username,
                'displayname' => $user->displayname,
            ];
        });
    }

    /**
     * Returns all users which have been active in the last 6 months
     * Data format: [ [userId => amountOfGames], ... ].
     *
     * @param $appId
     * @return \Illuminate\Support\Collection
     */
    public function findActiveUsers($appId)
    {
        // Get players which started a game in the last 6 months
        $proactivePlayers = \DB::table('games')
            ->select(\DB::raw('COUNT(*) as c'), 'player1_id')
            ->where('app_id', $appId)
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->groupBy('player1_id')
            ->pluck('c', 'player1_id');

        // Get players which have been challenged and responded
        $activePlayers = \DB::table('games')
            ->select(\DB::raw('COUNT(*) as c'), 'player2_id')
            ->where('app_id', $appId)
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->where('created_at', '<=', Carbon::now()->subDay())
            // Take only games which are finished or where it's player1s turn again (meaning player 2 played their round)
            ->whereIn('status', [Game::STATUS_FINISHED, Game::STATUS_TURN_OF_PLAYER_1])
            ->groupBy('player2_id')
            ->pluck('c', 'player2_id');

        // Merge the two arrays
        $proactivePlayers->each(function ($count, $playerId) use (&$activePlayers) {
            $activePlayers->put($playerId, $activePlayers->get($playerId, 0) + $count);
        });

        return $proactivePlayers;
    }

    public function userFilterQuery(int $appId, string $search = null, $tags = null, $filter = null, $orderBy = null, $descending = false, $showPersonalData = false, $showEmails = false)
    {
        /** @var Builder $userQuery */
        $userQuery = User::ofApp($appId)
            ->tagRights()
            ->where('is_dummy', false)
            ->where('is_api_user', false);

        if ($search) {
            $userQuery->where(function ($query) use ($appId, $search, $showEmails, $showPersonalData) {
                $app = App::findOrFail($appId);
                $metafields = $app->getUserMetaDataFields($showPersonalData);

                $query->where('id', extractHashtagNumber($search));
                $query->orWhereRaw('username LIKE ?', '%'.escapeLikeInput($search).'%');
                if (sizeof($metafields)) {
                    $query->orWhereHas('metafields', function($subquery) use ($metafields, $search) {
                        $subquery
                            ->whereIn('key', array_keys($metafields))
                            ->whereRaw('value LIKE ?', '%'.escapeLikeInput($search).'%');
                    });
                }
                if ($showPersonalData) {
                    $query->orWhereRaw('CONCAT_WS(\' \', firstname, lastname) LIKE ?', '%'.escapeLikeInput($search).'%');
                    if ($showEmails) {
                        $query->orWhereRaw('email LIKE ?', '%'.escapeLikeInput($search).'%');
                    }
                }
            });
        }

        if ($tags && count($tags)) {
            $addUsersWithoutTag = in_array(-1, $tags);
            $tags = array_filter($tags, function ($tag) {
                return $tag !== -1;
            });
            if (count($tags)) {
                $userQuery->where(function (Builder $query) use ($tags, $addUsersWithoutTag) {
                    $query->whereHas('tags', function ($query) use ($tags) {
                        $query->whereIn('tags.id', $tags);
                    });
                    if ($addUsersWithoutTag) {
                        $query->orWhereDoesntHave('tags');
                    }
                });
            } else {
                $userQuery->doesntHave('tags');
            }
        }

        if ($filter === 'deleted') {
            $userQuery->whereNotNull('deleted_at');
        } else {
            $userQuery->whereNull('deleted_at');
        }

        if(strpos($filter, 'role_') !== false) {
            $filterArray = explode('_', $filter);
            $roleId = (int) $filterArray[1];

            $userQuery
                ->where('user_role_id', $roleId)
                ->where('is_admin', 1);
        }

        switch ($filter) {
            case 'without_category':
                $userQuery->whereIn('id', (new ConfigurationLogicInvestigator)->usersCantPlayCategories($appId));
                break;
            case 'failed_login':
                $userQuery->where('failed_login_attempts', '>=', App::find($appId)->getMaxFailedLoginAttempts());
                break;
            case 'powerless_admins':
                $userQuery->powerlessAdmin();
                break;
            case 'admins':
                $userQuery->admin();
                break;
            case 'admins_with_tag_rights':
                $userQuery
                    ->where('is_admin', 1)
                    ->whereHas('tagRightsRelation');
                break;
            case 'inactive':
                $userQuery->where('active', 0);
                break;
            case 'tos_accepted':
                $userQuery->where('tos_accepted', 1);
                break;
            case 'tos_not_accepted':
                $userQuery->where('tos_accepted', 0);
                break;
            case 'tmp':
                $userQuery->where('email', 'LIKE', 'tmp%@sopamo.de');
                break;
            case 'main_admins':
                $userQuery->whereHas('role', function ($query) {
                    $query->where('is_main_admin', 1);
                });
        }

        // Add the subquery for voucher_expiration and expires_at_combined attribute
        $deletedAtCondition = 'IS NULL';
        if ($filter === 'deleted') {
            $deletedAtCondition = 'IS NOT NULL';
        }
        $userQuery->fromSub('SELECT
                `users`.*,
                IF(
                	MIN(IFNULL(`vouchers`.`validity_duration`, 0)) = 0,
                	NULL,
	                MAX(
	                    IF(
	                        `vouchers`.`validity_interval` = ' . Voucher::INTERVAL_MONTHS . ',
	                        DATE_ADD(`voucher_codes`.`cash_in_date`, INTERVAL `vouchers`.`validity_duration` MONTH),
	                        DATE_ADD(`voucher_codes`.`cash_in_date`, INTERVAL `vouchers`.`validity_duration` DAY)
	                    )
                    )
                ) as `voucher_expiration`
            FROM `users`
                LEFT JOIN `voucher_codes` ON `voucher_codes`.`user_id` = `users`.`id`
                LEFT JOIN `vouchers` ON `vouchers`.`id` = `voucher_codes`.`voucher_id`
            WHERE
                `users`.`deleted_at` ' . $deletedAtCondition . '
                AND users.app_id = ' . $appId . '
            GROUP BY `users`.`id`
        ', 'users');
        $userQuery->select([
            'users.*',
            \DB::raw('(SELECT MAX(created_at) FROM analytics_events v WHERE app_id = ' . $appId . ' AND user_id = users.id AND type != ' . AnalyticsEvent::TYPE_USER_CREATED . ') AS last_activity'),
            \DB::raw('IF(expires_at IS NOT NULL, expires_at, voucher_expiration) AS expires_at_combined'),
        ]);

        if ($orderBy) {
            $userQuery = User::query()
                ->fromSub($userQuery, 'users')
                ->orderBy($orderBy, $descending ? 'desc' : 'asc');
        }

        return $userQuery;
    }

    public function getQualificationHistory(User $user, User $adminUser)
    {
        $courseHistory = collect();
        $testHistory = collect();
        $coursesEngine = app(CoursesEngine::class);
        $settings = app(AppSettings::class, ['appId' => appId()]);

        if ($settings->getValue('module_tests') && $adminUser->hasRight('tests-stats')) {
            $accessibleTestIds = Test::ofApp(appId())->tagRights($adminUser)->pluck('id');
            $testSubmissions = $user->testSubmissions()
                ->whereIn('test_id', $accessibleTestIds)
                ->get()
                ->groupBy('test_id');
            $tests = Test::withTranslation()
                ->whereIn('id', $testSubmissions->keys())
                ->with('certificateTemplates')
                ->get()
                ->keyBy('id');

            foreach ($testSubmissions as $testSubmission) {
                $submission = $testSubmission
                    ->where('result', 1)
                    ->sortBy('updated_at')
                    ->first();

                if (!$submission) {
                    $submission = $testSubmission
                        ->where('result', 0)
                        ->sortByDesc('updated_at')
                        ->first();
                }

                if (!$submission) {
                    $submission = $testSubmission
                        ->sortByDesc('updated_at')
                        ->first();
                }

                $test = $tests->get($submission->test_id);

                $certificateLinks = [];
                if ($submission->result && $test->hasCertificateTemplate()) {
                    $certificateLinks[] = URL::signedRoute('certificateDownload', [
                        'submission_id' => $submission->id,
                    ]);
                }

                $testHistory->push([
                    'id' => $test->id,
                    'title' => $test->name,
                    'duration' => $test->minutes,
                    'status' => $submission->result,
                    'type' => 'test',
                    'date' => $submission->updated_at->toDateTimeString(),
                    'certificateLinks' => collect($certificateLinks),
                    'is_mandatory' => null,
                ]);
            }
        }

        if ($settings->getValue('module_courses') && $adminUser->hasRight('courses-stats')) {
            $userTags = $user->tags()->pluck('tags.id');
            $participatedCourseIds = CourseParticipation::where('user_id', $user->id)
                ->pluck('course_id');
            $accessibleCourses = Course
                ::where('app_id', appId())
                ->where(function ($query) use ($user, $participatedCourseIds, $userTags) {
                    $query->where(function($query) use ($userTags) {
                        $query->where('courses.has_individual_attendees', false)
                            ->where(function ($query) use ($userTags) {
                                $query->doesntHave('tags')
                                    ->orWhereHas('tags', function ($query) use ($userTags) {
                                        $query->whereIn('tags.id', $userTags);
                                    });
                            });
                    })
                    ->orWhere(function($query) use ($user) {
                        $query->where('courses.has_individual_attendees', true)
                            ->whereIn('courses.id', $user->individualCourses()->allRelatedIds());
                    })
                    ->orWhereIn('id', $participatedCourseIds);
                })
                ->tagRights($adminUser)
            ->get();
            $accessibleMandatoryCourses = $accessibleCourses->where('is_mandatory', 1);
            $accessibleCourseIds = $accessibleCourses->pluck('id');

            $courseParticipations = $user->courseParticipations()
                ->whereIn('course_id', $accessibleCourseIds)
                ->with('contentAttempts.content.chapter')
                ->get()
                ->groupBy('course_id');
            $courses = Course::withTranslation()
                ->whereIn('id', $courseParticipations->keys())
                ->get();
            $coursesEngine->attachCourseDurations($courses, $user);
            $courses = $courses->keyBy('id');

            foreach ($courseParticipations as $courseParticipation) {
                $participation = $courseParticipation
                    ->where('passed', 1)
                    ->sortBy('finished_at')
                    ->first();

                if (!$participation) {
                    $participation = $courseParticipation
                        ->where('passed', 0)
                        ->sortByDesc('updated_at')
                        ->first();
                }

                if (!$participation) {
                    $participation = $courseParticipation
                        ->sortByDesc('updated_at')
                        ->first();
                }

                $participationDate = $participation->created_at->toDateTimeString();
                if ($participation->finished_at) {
                    $participationDate = $participation->finished_at;
                }

                $certificateLinks = $participation->contentAttempts->filter(function (CourseContentAttempt $attempt) use ($adminUser, $coursesEngine) {
                    return $coursesEngine->hasAdminPermissionForCourseContent($adminUser, $attempt->content);
                })->transform(function (CourseContentAttempt $attempt) {
                    return $attempt->backend_certificate_download_url;
                })->whereNotNull();

                $course = $courses->get($participation->course_id);

                $courseHistory->push([
                    'id' => $participation->course->id,
                    'title' => $participation->course->title,
                    'duration' => $course->total_duration,
                    'status' => $participation->passed,
                    'type' => 'course',
                    'date' => $participationDate,
                    'certificateLinks' => $certificateLinks,
                    'is_mandatory' => $course->is_mandatory,
                ]);
            }

            foreach($accessibleMandatoryCourses as $accessibleMandatoryCourse) {
                if(!$courseParticipations->has($accessibleMandatoryCourse->id)) {
                    $courseHistory->push([
                        'id' => $accessibleMandatoryCourse->id,
                        'title' => $accessibleMandatoryCourse->title,
                        'duration' => $accessibleMandatoryCourse->total_duration,
                        'status' => null,
                        'type' => 'course',
                        'date' => null,
                        'certificateLinks' => collect([]),
                        'is_mandatory' => $accessibleMandatoryCourse->is_mandatory,
                    ]);
                }
            }
        }

        return collect([
                $courseHistory,
                $testHistory,
            ])->flatten(1)
            ->sortByDesc('date')
            ->values();
    }

    public function getUser($id)
    {
        $user = User
            ::where('is_dummy', false)
            ->tagRights()
            ->findOrFail($id);

        // Check access rights
        if ($user->app_id != appId()) {
            app()->abort(403);
        }

        return $user;
    }

    public function getUsersWithCombinedExpiresAtQuery(int $appId)
    {
        return User::query()
            ->select(
                DB::raw('users.*'),
                DB::raw('IF(
                    expires_at IS NOT NULL,
                    expires_at, # If the admin set an expiration date, we always want to use it
                    IF(
                        COUNT(IF(vouchers.validity_duration IS NULL, 1, null)) > 0, # Checks if at least one voucher has validity_duration that is null, e.g. unlimited
                        NULL, # If a voucher gives endless access, it means the user does not expire from vouchers
                        MAX(
                            IF(
                                `vouchers`.`validity_interval` = ' . Voucher::INTERVAL_MONTHS . ',
                                DATE_FORMAT( DATE_ADD(`voucher_codes`.`cash_in_date`, INTERVAL `vouchers`.`validity_duration` MONTH),"%Y-%m-%d"),
                                DATE_FORMAT( DATE_ADD(`voucher_codes`.`cash_in_date`, INTERVAL `vouchers`.`validity_duration` DAY),"%Y-%m-%d")
                            )
                        )
                    )
                ) AS expires_at_combined'),
            )
            ->leftJoin('voucher_codes', 'voucher_codes.user_id', '=', 'users.id')
            ->leftJoin('vouchers', 'vouchers.id', '=', 'voucher_codes.voucher_id')
            ->leftJoin('user_roles', 'users.user_role_id', '=', 'user_roles.id')
            ->where('users.app_id', $appId)
            ->whereNull('users.deleted_at')
            ->where('is_dummy', 0)
            ->where(function ($query) {
                return $query->whereNull('users.user_role_id')
                    ->orWhere('user_roles.is_main_admin', false);
            })
            ->havingRaw('expires_at_combined IS NOT NULL')
            ->groupBy('users.id');
    }
}
