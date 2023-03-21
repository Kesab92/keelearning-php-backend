<?php

namespace App\Services\Courses;

use App\Models\Courses\Course;
use App\Models\Courses\CourseContent;
use App\Models\Courses\CourseContentAttempt;
use App\Models\Forms\FormAnswer;
use App\Models\User;
use App\Services\MorphTypes;
use DB;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use URL;

class CourseStatisticsEngine
{
    public function getUsersProgress(Course $course, User $admin, $search, $tags, $filter, $orderBy, $orderDescending, $page, $perPage, $showPersonalData = false, $showEmails = false)
    {
        $usersQuery = $this->getFilteredEligibleUsersQuery($course, $admin, $search, $tags, $showPersonalData, $showEmails);

        $coursesEngine = app(CoursesEngine::class);

        // when hiding personal information from results,
        // only show user entries with at least a participation
        // to prevent extrapolating user information by
        // counting rows
        if (!$showPersonalData && !in_array($filter, ['participating', 'completed'])) {
            $filter = 'participating';
        }

        $usersQuery = DB::query()
            ->fromSub($usersQuery, 'users')
            ->select(['users.*', DB::raw('MAX(course_participations.id) as latest_participation'), DB::raw('MAX(course_participations.passed) as passed')])
            ->leftJoin('course_participations', function(Builder $q) use ($course) {
                $q->on('course_participations.user_id', '=', 'users.id')
                    ->where('course_participations.course_id', $course->id);
            })
            ->groupBy('users.id');

        switch ($filter) {
            case 'participating':
                $usersQuery->whereNotNull('course_participations.id');
                break;
            case 'completed':
                $usersQuery->havingRaw('MAX(course_participations.passed) IS NOT NULL');
        }

        $userCount = DB::query()
            ->fromSub($usersQuery, 'userlist')
            ->count();

        $usersQuery = DB::query()
            ->fromSub($usersQuery, 'users')
            ->select(['users.*', DB::raw('COUNT(*) as passed_attempts')])
            ->leftJoin('course_content_attempts', function(Builder $q) {
                $q->on('course_content_attempts.course_participation_id', '=', 'users.latest_participation')
                    ->where('course_content_attempts.passed', 1);
            })
            ->groupBy('users.id');

        // Special case when we want to sort by progress of a specific course_content
        if($orderBy && Str::startsWith($orderBy, 'progress_')) {
            $contentId = intval(str_replace('progress_', '', $orderBy));
            $usersQuery = DB::query()
                ->fromSub($usersQuery, 'users')
                ->select(['users.*', DB::raw('MAX(course_content_attempts.passed) as progress_' . $contentId)])
                ->leftJoin('course_content_attempts', function(Builder $q) use ($contentId) {
                    $q->on('course_content_attempts.course_participation_id', '=', 'users.latest_participation')
                        ->where('course_content_attempts.course_content_id', $contentId);
                })
                ->groupBy('users.id');
        }

        $users = $usersQuery
            ->orderBy($orderBy, $orderDescending ? 'desc' : 'asc')
            ->offset($perPage * ($page - 1))
            ->limit($perPage)
            ->get();

        $mostRecentParticipations = $course->participations()
            ->whereIn('user_id', $users->pluck('id'))
            ->whereIn('id', $users->pluck('latest_participation'))
            ->get()
            ->keyBy('user_id');

        $attempts = CourseContentAttempt
            ::whereIn('course_participation_id', $mostRecentParticipations->pluck('id'))
            ->with('content')
            ->select([
                DB::raw('MAX(id) as id'),
                DB::raw('MAX(finished_at) as finished_at'),
                'course_content_id',
                'course_participation_id',
                DB::raw('MAX(passed) as passed')
            ])
            ->orderBy('created_at', 'desc')
            ->groupBy(['course_content_id', 'course_participation_id'])
            ->get()
            ->groupBy('course_participation_id');

        $users->transform(function($user) use (
            $admin,
            $attempts,
            $coursesEngine,
            $mostRecentParticipations,
            $showEmails,
            $showPersonalData
        ) {
            $participation = $mostRecentParticipations->get($user->id, null);
            $data = [
                'attempts' => collect([]),
                'passed' => null,
                'finished_at' => null,
                'passed_25' => $user->passed_25 ?? '',
                'passed_attempts' => $user->passed_attempts,
                'course_participation_id' => null,
            ];

            if ($showPersonalData) {
                $data['id'] = $user->id;
                $data['username'] = $user->username;
                if ($showEmails) {
                    $data['email'] = $user->email;
                }
            }
            if($participation !== null) {
                $data['course_participation_id'] = $participation->id;
                $data['passed'] = $participation->passed;
                $data['finished_at'] = $participation->finished_at;
                $data['attempts'] = $attempts->get($participation->id, collect([]));
                if($data['attempts']) {
                    $data['attempts'] = $data['attempts']
                        ->filter(function($attempt) use ($coursesEngine, $admin) {
                            return $coursesEngine->hasAdminPermissionForCourseContent($admin, $attempt->content);
                        })
                        ->transform(function($attempt) use ($participation) {
                            if($attempt->passed && $attempt->content->type === CourseContent::TYPE_CERTIFICATE) {
                                $attempt->certificateDownloadURL = URL::route('courseCertificateDownloadInBackend', [
                                    'course_id' => $attempt->content->chapter->course_id,
                                    'participation_id' => $participation->id,
                                    'attempt_id' => $attempt->id,
                                ]);
                            }
                            $attempt->course_content = [
                                'isRepeatable' => $attempt->content->isRepeatable(),
                                'is_test' => $attempt->content->is_test,
                            ];
                            $attempt->unsetRelation('content');
                            return $attempt;
                        })->keyBy('course_content_id');
                }
            }

            return $data;
        });

        return [
            'count' => $userCount,
            'users' => $users,
        ];
    }

    /**
     * Returns all users eligible to take a course
     *
     * @param Course $course
     * @param User|null $admin Admin user for TAG access check
     * @return EloquentBuilder
     */
    public function getCourseEligibleUsersQuery(Course $course, ?User $admin = null): EloquentBuilder
    {
        $usersQuery = User::activeOfApp($course->app_id);

        if ($admin) {
            $usersQuery->tagRights($admin);
        }

        $usersQuery->leftJoin('tag_user', 'tag_user.user_id', 'users.id')
            ->select(['users.id', 'users.username', 'users.email', 'users.language', 'users.app_id'])
            ->groupBy('users.id');

        if(!$course->has_individual_attendees) {
            $courseTagIds = $course->tags()->allRelatedIds();
            if ($courseTagIds->count()) {
                $usersQuery->whereIn('tag_user.tag_id', $courseTagIds);
            }
        } else {
            $usersQuery->whereIn('users.id', $course->individualAttendees()->newPivotQuery()->select('user_id'));
        }

        return $usersQuery;
    }
    /**
     * Returns the count of all users eligible to take a course
     *
     * @param Course $course
     * @param User|null $admin Admin user for TAG access check
     * @return int
     */
    public function getCourseEligibleUsersCount(Course $course, ?User $admin = null): int
    {
        $query = $this->getCourseEligibleUsersQuery($course, $admin);
        $query->getQuery()->groups = null;
        return $query->distinct('users.id')
            ->count('users.id');
    }

    /**
     * Creates a query to select all ne potential users for a given course.
     *
     * @param Course $course
     * @param Collection $tagIds
     * @return EloquentBuilder
     */
    public function getNewPotentialCourseUsersQuery(Course $course, bool $hasIndividualAttendees, Collection $tagIds, Collection $individualAttendeeIds): EloquentBuilder
    {
        $usersQuery = User::activeOfApp($course->app_id);

        $usersQuery->leftJoin('tag_user', 'tag_user.user_id', 'users.id')
            ->select(['users.id', 'users.username', 'users.email', 'users.language', 'users.app_id'])
            ->groupBy('users.id');

        if(!$hasIndividualAttendees) {
            if($tagIds->count()) {
                $usersQuery->whereIn('tag_user.tag_id', $tagIds);
            }
        } else {
            $usersQuery->whereIn('users.id', $individualAttendeeIds);
        }

        $usersQuery->whereNotIn('users.id', $course->participations->pluck('user_id'));

        return $usersQuery;
    }

    /**
     * Returns all users eligible to take a course,
     * and those now ineligible but with prior participation,
     * optionally scoped to a given admin's TAG rights.
     *
     * @param Course $course
     * @param User|null $admin
     * @return EloquentBuilder
     */
    public function getCourseParticipatingUsersQuery(Course $course, ?User $admin = null): EloquentBuilder
    {
        $usersQuery = User::ofApp($course->app_id);

        if ($admin) {
            $usersQuery->tagRights($admin);
        }

        $usersQuery->leftJoin('tag_user', 'tag_user.user_id', 'users.id')
            ->select(['users.id', 'users.username', 'users.email'])
            ->groupBy('users.id');

        if(!$course->has_individual_attendees) {
            $courseTagIds = $course->tags()->pluck('tags.id');
            if ($courseTagIds->count()) {
                $usersQuery->leftJoin('course_participations', function($join) use ($course) {
                        $join->on('users.id', '=', 'course_participations.user_id')
                            ->where('course_participations.course_id', $course->id);
                    })
                    ->where(function ($query) use ($course, $courseTagIds) {
                        $query->where('course_participations.course_id', $course->id)
                            ->orWhereIn('tag_user.tag_id', $courseTagIds);
                    });
            }
        } else {
            $usersQuery->leftJoin('course_participations', function($join) use ($course) {
                $join->on('users.id', '=', 'course_participations.user_id')
                    ->where('course_participations.course_id', $course->id);
            })
            ->where(function ($query) use ($course) {
                $query->where('course_participations.course_id', $course->id)
                    ->orWhereIn('users.id', $course->individualAttendees()->newPivotQuery()->select('user_id'));
            });
        }

        return $usersQuery;
    }

    public function getCourseProgress(Course $course, User $admin) {
        $contentIds = $course->contents()->pluck('course_contents.id');

        $attemptCountsQuery = CourseContentAttempt
            ::leftJoin('course_participations', 'course_content_attempts.course_participation_id', '=', 'course_participations.id')
            ->joinSub($this->getCourseParticipatingUsersQuery($course, $admin), 'users_list', 'users_list.id', '=', 'course_participations.user_id', 'inner')
            ->whereIn('course_content_id', $contentIds)
            ->where('course_participations.course_id', $course->id)
            ->where('course_content_attempts.passed', 1)
            ->select('course_content_attempts.course_content_id')
            ->groupBy(['course_participations.user_id', 'course_content_attempts.course_content_id']);

        $attemptCounts = DB
            ::query()
            ->fromSub($attemptCountsQuery, 'attempts')
            ->select([DB::raw('COUNT(*) as c'), 'course_content_id'])
            ->groupBy('course_content_id')
            ->pluck('c', 'course_content_id');

        return $attemptCounts;
    }

    public function getFormAnswers(Course $course, CourseContent $courseContent, User $admin, $search, $tags, $orderBy, $orderDescending, $page, $perPage, $showPersonalData = false, $showEmails = false): array
    {
        $userQuery = $this->getFilteredEligibleUsersQuery($course, $admin, $search, $tags, $showPersonalData, $showEmails);

        $formAnswerQuery = FormAnswer
            ::select('form_answers.*')
            ->leftJoin(
                'course_content_attempts',
                'form_answers.foreign_id',
                '=',
                'course_content_attempts.id')
            ->where('form_answers.foreign_type', MorphTypes::TYPE_COURSE_CONTENT_ATTEMPT)
            ->where('course_content_attempts.course_content_id', $courseContent->id)
            ->whereIn('user_id', $userQuery->pluck('users.id'))
            ->with([
                'fields.formField',
                'user',
            ]);

        $formAnswerCount = $formAnswerQuery->count();

        if ($orderBy) {
            $formAnswerQuery->orderBy('form_answers.' . $orderBy, $orderDescending ? 'desc' : 'asc');
        }
        if ($page && $perPage) {
            $formAnswerQuery
                ->offset($perPage * ($page - 1))
                ->limit($perPage);
        }

        $formAnswers = $formAnswerQuery->get();

        return [
            'count' => $formAnswerCount,
            'formAnswers' => $formAnswers,
        ];
    }

    private function getFilteredEligibleUsersQuery(Course $course, User $admin, $search, $tags, $showPersonalData = false, $showEmails = false): EloquentBuilder
    {
        $usersQuery = $this->getCourseParticipatingUsersQuery($course, $admin);

        if (!$showPersonalData) {
            // we need to disable the search for obvious reasons
            $tags = [];
            $search = null;
        }

        if ($search) {
            $usersQuery->where(function ($query) use ($course, $search, $showEmails, $showPersonalData) {
                $query->whereRaw('users.username LIKE ?', '%'.escapeLikeInput($search).'%')
                    ->orWhere('users.id', extractHashtagNumber($search));
                $metafields = $course->app->getUserMetaDataFields($showPersonalData);
                if (sizeof($metafields)) {
                    $query->orWhereHas('metafields', function ($subquery) use ($metafields, $search) {
                        $subquery
                            ->whereIn('key', array_keys($metafields))
                            ->whereRaw('value LIKE ?', '%' . escapeLikeInput($search) . '%');
                    });
                }
                if ($showEmails) {
                    $query->orWhereRaw('users.email LIKE ?', '%'.escapeLikeInput($search).'%');
                }
            });
        }

        if (count($tags)) {
            $usersQuery->whereIn('tag_user.tag_id', $tags);
        }

        return $usersQuery;
    }
}
