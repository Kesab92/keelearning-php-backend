<?php

namespace App\Http\Controllers\BackendApi;

use App\Duplicators\Duplicator;
use App\Http\APIError;
use App\Http\Controllers\Controller;
use App\Http\Requests\BackendApi\CourseUpdateRequest;
use App\Models\AccessLog;
use App\Models\App;
use App\Models\ContentCategories\ContentCategory;
use App\Models\Courses\Course;
use App\Models\Courses\CourseContent;
use App\Models\Courses\CourseParticipation;
use App\Models\Tag;
use App\Models\User;
use App\Services\AccessLogEngine;
use App\Services\AccessLogMeta\Courses\AccessLogCourseDelete;
use App\Services\CourseReminderEngine;
use App\Services\Courses\CourseContentsEngine;
use App\Services\Courses\CoursesEngine;
use App\Services\Courses\CourseStatisticsEngine;
use App\Services\ImageUploader;
use App\Services\StatsServerEngine;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Response;

class CoursesController extends Controller
{
    const COURSES_ORDER_BY = [
        'id',
        'available_from',
        'visible',
        'is_mandatory',
        'title',
    ];
    const TEMPLATES_ORDER_BY = [
        'id',
        'title',
        'next_repetition_date',
    ];
    const PER_PAGE = [
        50,
        100,
        200,
    ];
    const FILTERS = [
        'active',
        'archived',
        'expired',
    ];
    const FILTERS_TEMPLATES = [
        'archived',
        'invisible',
        'is_not_repeating',
        'is_repeating',
        'visible',
    ];
    /**
     * @var CoursesEngine
     */
    private CoursesEngine $coursesEngine;

    public function __construct(CoursesEngine $coursesEngine)
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:courses,courses-edit|courses-view');
        $this->middleware('auth.backendaccess:courses,courses-edit')->only([
            'archive',
            'cover',
            'create',
            'delete',
            'deleteInformation',
            'unarchive',
            'update',
            'updateManagers',
            'usersToNotify',
        ]);
        $this->coursesEngine = $coursesEngine;
    }

    /**
     * Returns the course list.
     * @return JsonResponse
     */
    public function index(Request $request, CoursesEngine $coursesEngine):JsonResponse
    {
        $getTemplates = !!$request->input('templates');
        $orderBy = $request->input('sortBy');

        if($getTemplates) {
            if (! in_array($orderBy, self::TEMPLATES_ORDER_BY)) {
                $orderBy = self::TEMPLATES_ORDER_BY[0];
            }
        } else{
            if (! in_array($orderBy, self::COURSES_ORDER_BY)) {
                $orderBy = self::COURSES_ORDER_BY[0];
            }
        }
        $orderDescending = $request->input('descending') === 'true';
        $page = (int) $request->input('page') ?? 1;
        $perPage = $request->input('rowsPerPage');
        if (! in_array($perPage, self::PER_PAGE)) {
            $perPage = self::PER_PAGE[0];
        }
        $filter = $request->input('filter');
        if (!in_array($filter, $getTemplates ? self::FILTERS_TEMPLATES : self::FILTERS)) {
            $filter = null;
        }
        $search = $request->input('search');
        $tags = $request->input('tags', []);
        $categories = $request->input('categories', []);

        if($getTemplates) {
            // we sort at application-level for course templates
            $coursesQuery = $coursesEngine->courseFilterQuery(appId(), $search, $tags, $filter, $categories, null, null, false, $getTemplates);
        } else {
            $coursesQuery = $coursesEngine->courseFilterQuery(appId(), $search, $tags, $filter, $categories, $orderBy, $orderDescending, false, $getTemplates);
        }

        $coursesCount = $coursesQuery->count();

        $with = [
            'categories',
            'creator',
            'tags',
            'translationRelation',
        ];

        if($getTemplates) {
            $with[] = 'latestRepeatedCourse';

            $courses = $coursesQuery
                ->with($with)
                ->get();

            if($orderBy) {
                switch ($orderBy) {
                    case 'title':
                        $courses = $courses->sortBy('title', SORT_REGULAR, $orderDescending);
                        break;
                    case 'next_repetition_date':
                        $courses = $courses->sort(function ($courseA, $courseB) use ($orderDescending) {
                            if ($courseA->nextRepetitionDate == $courseB->nextRepetitionDate) {
                                return 0;
                            }
                            if (!$courseA->nextRepetitionDate) {
                                return 1;
                            }
                            if (!$courseB->nextRepetitionDate) {
                                return -1;
                            }
                            if ($orderDescending) {
                                $modifier = -1;
                            } else {
                                $modifier = 1;
                            }
                            return $courseA->nextRepetitionDate > $courseB->nextRepetitionDate ? 1 * $modifier : -1 * $modifier;
                        });
                        break;
                }
            }

            $courses = $courses
                ->skip($perPage * ($page - 1))
                ->take($perPage);
            $courseParticipationCounts = collect();
        } else {
            $courses = $coursesQuery
                ->with($with)
                ->offset($perPage * ($page - 1))
                ->limit($perPage)
                ->get();
            $courseParticipationCounts = DB::query()
                ->fromSub(
                    CourseParticipation::whereIn('course_id', $courses->pluck('id'))
                        ->select(['user_id', 'course_id', DB::raw('COUNT(*) as c')])
                        ->groupBy(['course_id', 'user_id']),
                    'counts'
                )
                ->select('course_id', DB::raw('SUM(c) as sum'))
                ->groupBy('counts.course_id')
                ->pluck('sum', 'course_id');
        }

        $individualAttendeeCount = DB::table('course_individual_attendees')
            ->select('course_id', DB::raw('COUNT(*) as count'))
            ->whereIn('course_id', $courses->pluck('id'))
            ->groupBy('course_id')
            ->pluck('count', 'course_id');

        $statsOptions = [
            'courses' => [
                'only' => ['coursesUserCounts','coursePassed'],
            ],
        ];

        $statsResponse = (new StatsServerEngine)->getStats($statsOptions, appId());

        $courses->transform(function ($course) use ($individualAttendeeCount, $courseParticipationCounts, $statsResponse) {
            return $this->formatCourseListEntry($course, $courseParticipationCounts, $individualAttendeeCount,$statsResponse);
        });

        return response()->json([
            'count' => $coursesCount,
            'courses' => $courses->values(),
        ]);
    }

    public function getAllTemplates(CoursesEngine $coursesEngine):JsonResponse
    {
        return response()->json([
            'templates' => $coursesEngine->getTemplates(appId()),
        ]);
    }

    /**
     * Creates a course.
     *
     * @param Request $request
     * @param CourseContentsEngine $courseContentsEngine
     * @return JsonResponse
     * @throws \Throwable
     */
    public function create(Request $request, CourseContentsEngine $courseContentsEngine):JsonResponse
    {
        $tags = $request->input('tags', []);
        $template = null;
        if ($templateId = $request->input('template')) {
            $template = Course::template()
                ->where('id', $templateId)
                ->where('visible', true)
                ->where(function ($query) {
                    $query->where('app_id', appId());
                    if (env('GLOBAL_TEMPLATE_APP_ID') !== null) {
                        $query->orWhere('app_id', env('GLOBAL_TEMPLATE_APP_ID'));
                    }
                    $query->orWhereIn('id', App::find(appId())
                        ->inheritedCourseTemplates()
                        ->select('course_id')
                        ->where('visible', true));
                })
                ->firstOrFail();
        }

        DB::beginTransaction();
        if ($template) {
            try {
                $course = $template->duplicate(appId());
            } catch (Exception $e) {
                DB::rollback();
                return new APIError($e->getMessage());
            }
        } else {
            $course = new Course();
            $course->setLanguage(defaultAppLanguage(appId()));
        }
        $course->app_id = appId();
        $course->is_template = !!$request->input('is_template');
        $course->creator_id = Auth::user()->id;
        $course->title = $request->input('title');
        $course->visible = false;
        $course->save();

        $course->tags()->detach();
        $course->syncTags($tags);

        if (!$course->chapters()->count()) {
            $courseContentsEngine->createChapter($course);
        }
        DB::commit();

        return Response::json([
            'course' => $this->getCourseResponse($course->id),
            'warnings' => Duplicator::getWarnings()->pluck('type')->unique(),
        ]);

    }

    /**
     * Returns the course.
     *
     * @param int $courseId
     * @return JsonResponse
     */
    public function show(int $courseId): JsonResponse
    {
        return Response::json([
            'course' => $this->getCourseResponse($courseId),
        ]);
    }

    /**
     * Updates the cover image.
     *
     * @param int $courseId
     * @param Request $request
     * @param ImageUploader $imageUploader
     * @return JsonResponse
     */
    public function cover(int $courseId, Request $request, ImageUploader $imageUploader): JsonResponse
    {
        $course = $this->coursesEngine->getCourse($courseId, Auth::user());

        $file = $request->file('file');
        if (! $imageUploader->validate($file)) {
            app()->abort(403);
        }

        // TODO: delete unused course cover images
        if (! $imagePath = $imageUploader->upload($file, 'uploads/course-cover')) {
            app()->abort(400);
        }
        $imagePath = formatAssetURL($imagePath, '3.0.0');

        return \Response::json([
            'image' => $imagePath,
        ]);
    }

    /**
     * Clones the course.
     * @param int $courseId
     * @return JsonResponse
     * @throws \Throwable
     */
    public function clone(int $courseId):JsonResponse
    {
        $course = $this->coursesEngine->getCourse($courseId, Auth::user());
        $newCourse = $course->duplicate();
        $newCourse->creator_id = Auth::user()->id;
        $newCourse->title = 'Kopie von ' . $newCourse->title;
        $newCourse->save();

        return Response::json([
            'course_id' => $newCourse->id,
        ]);
    }

    /**
     * Clones the course as a template.
     * @param int $courseId
     * @return JsonResponse
     * @throws \Throwable
     */
    public function cloneAsTemplate(int $courseId):JsonResponse
    {
        $course = $this->coursesEngine->getCourse($courseId, Auth::user());
        $newCourse = $course->duplicate();
        $newCourse->creator_id = Auth::user()->id;
        $newCourse->title = 'Vorlage aus ' . $newCourse->title;
        $newCourse->is_template = 1;
        $newCourse->save();

        return Response::json([
            'course_id' => $newCourse->id,
        ]);
    }

    /**
     * Updates the course.
     *
     * @param int $courseId
     * @param CourseUpdateRequest $request
     * @param CoursesEngine $coursesEngine
     * @param CourseReminderEngine $courseReminderEngine
     * @return JsonResponse
     * @throws \Throwable
     */
    public function update(int $courseId, CourseUpdateRequest $request, CoursesEngine $coursesEngine, CourseReminderEngine $courseReminderEngine): JsonResponse
    {
        $user = Auth::user();
        $course = $this->coursesEngine->getCourse($courseId, $user);

        $oldAvailableStatus = $course->availableStatus;

        DB::transaction(function() use ($courseReminderEngine, $course, $request) {
            $basicFields = [
                'cover_image_url',
                'description',
                'duration_type',
                'is_mandatory',
                'is_repeating',
                'participation_duration',
                'participation_duration_type',
                'preview_enabled',
                'repetition_count',
                'repetition_interval',
                'repetition_interval_type',
                'request_access_link',
                'send_new_course_notification',
                'send_passed_course_mail',
                'send_repetition_course_reminder',
                'time_limit',
                'time_limit_type',
                'title',
                'visible',
            ];
            foreach ($basicFields as $field) {
                if ($request->has($field)) {
                    $value = $request->input($field, null);
                    $course->setAttribute($field, $value);
                }
            }

            if($request->has('available_from')) {
                if ($request->input('available_from')) {
                    $course->available_from = Carbon::parse($request->input('available_from'))->startOfDay();
                } else {
                    $course->available_from = null;
                }
            }

            if($request->has('available_until')) {
                if ($request->input('available_until')) {
                    $course->available_until = Carbon::parse($request->input('available_until'))->endOfDay();
                } else {
                    $course->available_until = null;
                }
            }

            if($request->has('templateInheritanceApps')) {
                $course->templateInheritanceApps()->sync($request->input('templateInheritanceApps'));
            }

            // Changing the individual attendee setting is only allowed if the admin won't remove access to users
            // that they are not allowed to manage.
            if($request->has('has_individual_attendees')) {
                $canEditAccessType = true;
                $admin = Auth::user();
                $newHasIndividualAttendees = intval($request->input('has_individual_attendees'));
                if(!$admin->isFullAdmin()) {
                    if($newHasIndividualAttendees !== $course->has_individual_attendees) {
                        if($newHasIndividualAttendees) {
                            // The course should be changed to use individual attendees
                            // This is only allowed if the admin is allowed to manage all TAGs that are currently set for the course
                            $courseTagIds = $course->tags()->allRelatedIds();
                            $courseHasReadonlyTags = $admin->tagRightsRelation()->allRelatedIds()->intersect($courseTagIds)->count() !== $courseTagIds->count();
                            if($courseHasReadonlyTags) {
                                $canEditAccessType = false;
                            }
                        } else {
                            // The course should be changed to use TAGs for access control
                            // This is only allowed if the admin is allowed to manage all users that currently have access to the course
                            $readonlyAttendees = $course->individualAttendees->filter(function(User $user) use ($admin) {
                                return !$user->isAccessibleByAdmin($admin);
                            });
                            if($readonlyAttendees->isNotEmpty()) {
                                $canEditAccessType = false;
                            }
                        }
                    }
                }
                if($canEditAccessType) {
                    $course->has_individual_attendees = $newHasIndividualAttendees;
                }
            }

            if ($course->is_repeating && !$course->available_from) {
                abort(403);
            }

            $course->save();

            $tagFields = [
                'tags' => 'tags',
                'award_tags' => 'awardTags',
                'retract_tags' => 'retractTags',
                'preview_tags' => 'previewTags',
            ];
            foreach ($tagFields as $requestField => $relationship) {
                if ($request->has($requestField)) {
                    $allowEmptyTags = true;
                    if($relationship === 'tags') {
                        $allowEmptyTags = false;
                    }
                    $course->syncTags($request->input($requestField) ?: [], $relationship, $allowEmptyTags);
                }
            }

            if($request->has('categories')) {
                $categories = $request->input('categories', []);
                if (!is_array($categories)) {
                    $categories = [$categories];
                }
                $availableCategories = ContentCategory
                    ::where('app_id', $course->app_id)
                    ->where('type', ContentCategory::TYPE_COURSES)
                    ->pluck('id');

                $categoryIds = collect($categories)->intersect($availableCategories);
                $course->categories()->syncWithPivotValues($categoryIds, [
                    'type' => ContentCategory::TYPE_COURSES,
                ]);
            }

            if($request->has('managers')) {
                $managers = collect($request->input('managers', []));

                $availableAdmins = User::ofApp(appId())
                    ->tagRights()
                    ->where('is_dummy', false)
                    ->where('is_api_user', false)
                    ->where('is_admin', true)
                    ->get()
                    ->pluck('id');

                $managersIds = collect($managers)->intersect($availableAdmins);
                $course->managers()->sync($managersIds);
            }

            if($request->has('individualAttendees')) {
                $newIndividualAttendeeIds = collect($request->input('individualAttendees', []));
                $currentAttendees = $course->individualAttendees;
                $currentAttendees->load('tags');

                // Remove attendees that the admin is allowed to remove
                $attendeesToRemove = $currentAttendees->whereNotIn('id', $newIndividualAttendeeIds);
                $attendeesToRemove = $attendeesToRemove->filter(function(User $user) {
                    return $user->isAccessibleByAdmin();
                });
                $course->individualAttendees()->detach($attendeesToRemove);

                // Add attendees that the admin is allowed to add
                $attendeeIdsToAdd = $newIndividualAttendeeIds->filter(function($id) use ($currentAttendees) {
                    return !$currentAttendees->contains($id);
                });
                $attendeesToAdd = User
                    ::ofApp(appId())
                    ->tagRights()
                    ->whereIn('id', $attendeeIdsToAdd)
                    ->get();
                $course->individualAttendees()->attach($attendeesToAdd);
            }

            if($request->has('reminderEmails') && $course->reminders()->exists()) {
                $emails = parseEmails($request->input('reminderEmails', []));
                $courseReminderEngine->updateEmails($course->id, $emails);
            }
        });

        $newAvailableStatus = $course->availableStatus;

        if(
            !$course->is_template
            && $course->send_new_course_notification
            && !$oldAvailableStatus
            && $newAvailableStatus
        ) {
            $coursesEngine->notifyAboutNewCourse($course);
        }

        return Response::json(['course' => $this->getCourseResponse($courseId)]);
    }

    /**
     * @param int $courseId
     * @return JsonResponse
     */
    public function deleteInformation(int $courseId):JsonResponse
    {
        $course = $this->coursesEngine->getCourse($courseId, Auth::user());
        return Response::json([
            'dependencies' => $course->safeRemoveDependees(),
            'blockers' => $course->getBlockingDependees(),
        ]);
    }

    /**
     * @param int $courseId
     * @param AccessLogEngine $accessLogEngine
     * @return JsonResponse
     * @throws \Throwable
     */
    public function delete(int $courseId, AccessLogEngine $accessLogEngine):JsonResponse
    {
        $course = $this->coursesEngine->getCourse($courseId, Auth::user());

        $result = DB::transaction(function() use ($accessLogEngine, $course) {
            $accessLogEngine->log(AccessLog::ACTION_DELETE_COURSE, new AccessLogCourseDelete($course), Auth::user()->id);
            return $course->safeRemove();
        });

        if($result->isSuccessful()) {
            return Response::json([], 204);
        } else {
            return Response::json($result->getMessages(), 400);
        }
    }

    /**
     * Archives the course.
     * @param int $courseId
     * @return JsonResponse
     */
    public function archive(int $courseId): JsonResponse
    {
        $course = $this->coursesEngine->getCourse($courseId, \Auth::user());

        if($course->is_template) {
            abort(404);
        }

        $course->archived_at = Carbon::now();
        $course->save();

        return Response::json(['course' => $this->getCourseResponse($courseId)]);
    }

    /**
     * Unarchives the course.
     * @param int $courseId
     * @return JsonResponse
     */
    public function unarchive(int $courseId): JsonResponse
    {
        $course = $this->coursesEngine->getCourse($courseId, \Auth::user());
        $course->archived_at = null;
        $course->save();

        return Response::json(['course' => $this->getCourseResponse($courseId)]);
    }

    /**
     * Users to notify about a new course.
     *
     * @param int $courseId
     * @param Request $request
     * @param CourseStatisticsEngine $courseStatisticsEngine
     * @return JsonResponse
     */
    public function usersToNotify(int $courseId, Request $request, CourseStatisticsEngine $courseStatisticsEngine): JsonResponse
    {
        $course = $this->coursesEngine->getCourse($courseId, Auth::user());

        $tags = $request->input('tags', []);
        $availableTags = Tag
            ::where('app_id', $course->app_id)
            ->whereIn('id', $tags)
            ->pluck('id');
        $tagIds = collect($tags)->intersect($availableTags);

        $hasIndividualAttendees = $request->input('hasIndividualAttendees');
        $individualAttendeeIds = collect($request->input('individualAttendees', []));

        $userCount = $courseStatisticsEngine->getNewPotentialCourseUsersQuery($course, $hasIndividualAttendees, $tagIds, $individualAttendeeIds)->get()->count();

        return Response::json(['count' => $userCount]);
    }

    /**
     * Returns the course response.
     * @param int $courseId
     * @return array
     */
    private function getCourseResponse(int $courseId): array
    {
        $course = $this->coursesEngine->getCourse($courseId, Auth::user());

        $relations = [
            'chapters.contents.tags',
            'chapters.contents.translationRelation',
            'chapters.translationRelation',
        ];
        if (language() != defaultAppLanguage()) {
            $relations[] = 'chapters.contents.defaultTranslationRelation';
            $relations[] = 'chapters.defaultTranslationRelation';
        }
        $course->load($relations);
        $this->coursesEngine->loadRelatableTranslations($course);

        $courseStatisticsEngine = app(CourseStatisticsEngine::class);

        $eligibleUserCount =  $courseStatisticsEngine->getCourseEligibleUsersCount($course);
        $participationUserCount =  CourseParticipation
            ::where('course_id', $courseId)
            ->count(DB::raw('DISTINCT user_id'));
        $finishedParticipationUserCount =  CourseParticipation
            ::where('course_id', $courseId)
            ->whereNotNull('passed')
            ->count(DB::raw('DISTINCT user_id'));
        $passedParticipationUserCount =  CourseParticipation
            ::where('course_id', $courseId)
            ->where('passed', 1)
            ->count(DB::raw('DISTINCT user_id'));

        $finishedParticipationUserPercentage = 0;
        $passedParticipationUserPercentage = 0;

        if($eligibleUserCount) {
            $finishedParticipationUserPercentage = floor($finishedParticipationUserCount / $eligibleUserCount * 100);
            $passedParticipationUserPercentage = floor($passedParticipationUserCount / $eligibleUserCount * 100);
        }

        $templateInheritanceApps = $course
            ->templateInheritanceApps()
            ->pluck('course_template_inheritances.app_id');

        $currentRepetitionCount = Course
            ::where('app_id', $course->app_id)
            ->where('parent_course_id', $course->id)
            ->count();

        $parent = null;
        if ($course->parent) {
            $parent = [
                'id' => $course->parent->id,
                'nextRepetitionDate' => $course->parent->nextRepetitionDate,
                'title' => $course->parent->title,
            ];
        }

        $chapters = $course->chapters->map(function ($chapter) {
            $contents = $chapter->contents->map(function ($content) {
                $relatable = null;
                if (in_array($content->type, [CourseContent::TYPE_LEARNINGMATERIAL, CourseContent::TYPE_FORM]) && $content->relatable) {
                    $relatable = [
                        'id' => $content->relatable->id,
                        'title' => $content->relatable->title,
                    ];
                    if ($content->relatable->wbt_id) {
                        $relatable['wbt_id'] = $content->relatable->wbt_id;
                    }
                };
                if ($content->type === CourseContent::TYPE_APPOINTMENT && $content->relatable) {
                    $relatable = [
                        'id' => $content->relatable->id,
                        'title' => $content->relatable->name,
                    ];
                }
                return [
                    'course_chapter_id' => $content->course_chapter_id,
                    'duration' => $content->duration,
                    'id' => $content->id,
                    'position' => $content->position,
                    'relatable' => $relatable,
                    'tags' => $content->tags,
                    'title' => $content->title,
                    'type' => $content->type,
                    'is_test' => $content->is_test,
                    'visible' => $content->visible,
                ];
            });
            return [
                'id' => $chapter->id,
                'contents' => $contents,
                'title' => $chapter->title,
            ];
        });

        return [
            'archived_at' => $course->archived_at,
            'available_from' => $course->available_from ? $course->available_from->toDateTimeString() : null,
            'available_status' => $course->availableStatus,
            'available_until' => $course->available_until ? $course->available_until->toDateTimeString() : null,
            'award_tags' => $course->awardTags()->allRelatedIds(),
            'categories' => $course->categories()->allRelatedIds(),
            'chapters' => $chapters,
            'cover_image_url' => $course->cover_image_url,
            'created_at' => $course->created_at->toDateTimeString(),
            'creator' => $course->creator ? $course->creator->getDisplaynameBackend() : null,
            'current_repetition_count' => $currentRepetitionCount,
            'description' => $course->description,
            'duration_type' => $course->duration_type,
            'finishedParticipationUserPercentage' => $finishedParticipationUserPercentage,
            'frontendUrl' => $course->getFrontendUrl(Auth::user()),
            'hasReminders' => $course->reminders()->exists(),
            'id' => $course->id,
            'is_mandatory' => $course->is_mandatory,
            'is_repeating' => $course->is_repeating,
            'is_template' => $course->is_template,
            'latestRepeatedCourseCreatedAt' => $course->latestRepeatedCourse ? $course->latestRepeatedCourse->created_at->toDateTimeString() : null,
            'managers' => $course->managers()->allRelatedIds(),
            'parent' => $parent,
            'participation_duration' => $course->participation_duration,
            'participation_duration_type' => $course->participation_duration_type,
            'eligibleUserCount' => $eligibleUserCount,
            'participationUserCount' => $participationUserCount,
            'passedParticipationUserPercentage' => $passedParticipationUserPercentage,
            'preview_enabled' => $course->preview_enabled,
            'preview_tags' => $course->previewTags()->allRelatedIds(),
            'repetition_count' => $course->repetition_count,
            'repetition_interval_type' => $course->repetition_interval_type,
            'repetition_interval' => $course->repetition_interval,
            'request_access_link' => $course->request_access_link,
            'retract_tags' => $course->retractTags()->allRelatedIds(),
            'send_new_course_notification' => $course->send_new_course_notification,
            'send_passed_course_mail' => $course->send_passed_course_mail,
            'send_repetition_course_reminder' => $course->send_repetition_course_reminder,
            'tags' => $course->tags()->allRelatedIds(),
            'templateInheritanceApps' => $templateInheritanceApps,
            'time_limit_type' => $course->time_limit_type,
            'time_limit' => $course->time_limit,
            'title' => $course->title,
            'translations' => $course->allTranslationRelations->values(),
            'visible' => $course->visible,
            'has_individual_attendees' => $course->has_individual_attendees,
            'individualAttendees' => $course->individualAttendees()->allRelatedIds(),
        ];
    }

    /**
     * Formats the course entry for the list.
     *
     * @param Course $course
     * @param Collection $courseParticipationCounts
     * @param Collection $individualAttendeeCounts
     * @param array $statsResponse
     * @return array
     */
    private function formatCourseListEntry(Course $course, Collection $courseParticipationCounts, Collection $individualAttendeeCounts, array $statsResponse): array
    {
        $courseUserCounts = collect ($statsResponse['courses']['coursesUserCounts']);
        $coursePassed = collect ($statsResponse['courses']['coursePassed']);
        $courseId = $course->id;
        $passedUserCountOfCourseId = $coursePassed->get($courseId);
        $totalUserCountOfCourseId = $courseUserCounts->get($courseId);


        return [
            'id'                       => $course->id,
            'title'                    => $course->title,
            'available_from'           => $course->available_from,
            'available_until'          => $course->available_until,
            'categories'               => $course->categories->pluck('id'),
            'cover_image_url'          => $course->cover_image_url,
            'created_at'               => $course->created_at,
            'is_mandatory'             => $course->is_mandatory,
            'is_repeating'             => $course->is_repeating,
            'is_template'              => $course->is_template,
            'next_repetition_date'     => $course->next_repetition_date,
            'participations'           => $courseParticipationCounts->get($course->id, 0),
            'repetition_count'         => $course->repetition_count,
            'repetition_interval'      => $course->repetition_interval,
            'repetition_interval_type' => $course->repetition_interval_type,
            'tags'                     => $course->tags,
            'visible'                  => $course->visible,
            'archived_at'              => $course->archived_at,
            'user_finished_percentage' => $passedUserCountOfCourseId ? (int)$passedUserCountOfCourseId / $totalUserCountOfCourseId:0,
            'user_count_total'         => $totalUserCountOfCourseId,
            'user_count_passed'        => $passedUserCountOfCourseId ? : 0,
            'latestRepeatedCourseCreatedAt' => $course->latestRepeatedCourse ? $course->latestRepeatedCourse->created_at : null,
            'has_individual_attendees' => $course->has_individual_attendees,
            'individualAttendeesCount' => $individualAttendeeCounts->get($course->id, 0),
        ];
    }
}
