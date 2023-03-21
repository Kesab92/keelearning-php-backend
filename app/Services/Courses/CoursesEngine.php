<?php

namespace App\Services\Courses;

use App\Mail\Mailer;
use App\Models\AnalyticsEvent;
use App\Models\App;
use App\Models\Courses\Course;
use App\Models\Courses\CourseAccessRequest;
use App\Models\Courses\CourseChapter;
use App\Models\Courses\CourseContent;
use App\Models\Courses\CourseContentAttachment;
use App\Models\Courses\CourseContentAttempt;
use App\Models\Courses\CourseParticipation;
use App\Models\TodolistItem;
use App\Models\User;
use App\Services\QuestionsEngine;
use App\Services\TranslationEngine;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Date;

class CoursesEngine
{
    /**
     * Fetches a single course by ID,
     * checking for tagRights & app permissions.
     *
     * @param int $id
     * @param User $user
     * @return Course
     */
    public function getCourse(int $id, User $user): Course
    {
        $course = Course::tagRights($user)->withTemplates()->findOrFail($id);
        if ($course->app_id !== appId()) {
            app()->abort(404);
        }

        return $course;
    }

    public function getTemplates(int $appId): array
    {
        $hasGlobal = env('GLOBAL_TEMPLATE_APP_ID') != null && $appId != env('GLOBAL_TEMPLATE_APP_ID');
        $globalTemplates = collect([]);
        if($hasGlobal) {
            $globalTemplates = $this->courseTemplateQuery()->where('app_id', env('GLOBAL_TEMPLATE_APP_ID'))->get();
        }

        $localTemplates = $this->courseTemplateQuery()->where('app_id', $appId)->get();

        $thirdPartyTemplates = App::find($appId)
            ->inheritedCourseTemplates()
            ->where('visible', true)
            ->with(['chapters', 'contents'])
            ->get();

        return [
            'global' => $globalTemplates,
            'local'  => $localTemplates,
            'thirdParty' => $thirdPartyTemplates,
        ];
    }

    private function courseTemplateQuery()
    {
        return Course::template()->where('visible', true)->with(['chapters', 'contents']);
    }

    /**
     * Returns the content of a given course, if the provided user has access.
     * This method only checks access to the content, not to the course itself!
     *
     * @param Course $course
     * @param integer $contentId
     * @param User $user
     * @return CourseContent
     */
    public function getCourseContent(Course $course, int $contentId, User $user) {
        $userTags = $user->tags->pluck('id');
        return $course
            ->contents()
            ->where('course_contents.visible', 1)
            ->where('course_contents.id', $contentId)
            ->where(function ($query) use ($userTags, $user) {
                $query->where(function ($query) use ($userTags) {
                    $query->doesntHave('tags')
                        ->orWhereHas('tags', function ($query) use ($userTags) {
                            $query->whereIn('tags.id', $userTags);
                        });
                });
            })
            ->first();
    }

    /**
     * Get all courses which are currently visible for the user.
     *
     * @param User $user
     * @param array|null $courseIds Optionally only return these courses
     * @param boolean $showHidden Shows courses that would otherwise be invisible, if user is admin
     * @return Collection|Course[]
     */
    public function getUsersCourses(User $user, $courseIds = null, $showHidden = false): Collection
    {
        $userTags = $user->tags()->pluck('tags.id');
        $query = Course::where('app_id', $user->app_id)
            ->where(function ($query) use ($user, $userTags) {
                $query
                    ->where(function($query) use ($userTags) {
                        // User has access via TAGs
                        $query->where('has_individual_attendees', false)
                            ->where(function ($query) use ($userTags) {
                            $query->doesntHave('tags')
                                ->orWhereHas('tags', function ($query) use ($userTags) {
                                    $query->whereIn('tags.id', $userTags);
                                });
                        });
                    })
                    ->orWhere(function($query) use ($user) {
                        // User has access via individual attendee access
                        $query->where('has_individual_attendees', true)
                            ->whereIn('courses.id', $user->individualCourses()->allRelatedIds());
                    })
                    ->orWhere(function ($query) use ($userTags) {
                        // User has access via preview and there are no preview TAGs
                        $query->where('preview_enabled', 1)
                            ->doesntHave('previewTags');
                    })
                    ->orWhere(function ($query) use ($userTags) {
                        // User has access via preview and they have matching preview TAGs
                        $query->where('preview_enabled', 1)
                            ->whereHas('previewTags', function ($query) use ($userTags) {
                                $query->whereIn('tags.id', $userTags);
                            });
                    });
            });

        if ($user->is_admin && $showHidden) {
            $query->withoutGlobalScope('not_template');
        } else {
            $participatedCourseIds = CourseParticipation::where('user_id', $user->id)
                ->pluck('course_id');
            $query->where('visible', 1)
                ->whereNull('archived_at')
                ->where(function (Builder $query) use ($participatedCourseIds) {
                    // Select courses which are available or which the user already participated in
                    $query->where(function (Builder $query) {
                        $query
                            ->where('courses.duration_type', Course::DURATION_TYPE_FIXED)
                            ->where(function (Builder $query) {
                                $query
                                    ->whereNull('courses.available_from')
                                    ->orWhere('courses.available_from', '<=', Carbon::now());
                            })->where(function (Builder $query) {
                                $query
                                    ->whereNull('courses.available_until')
                                    ->orWhere('courses.available_until', '>=', Carbon::now());
                            });
                    })
                    ->orWhere(function($query) {
                        $query->where('courses.duration_type', Course::DURATION_TYPE_DYNAMIC)
                            ->where(function (Builder $query) {
                                $query
                                    ->whereNull('courses.available_from')
                                    ->orWhere('courses.available_from', '<=', Carbon::now());
                            });
                    })
                    ->orWhereIn('id', $participatedCourseIds);
                });
        }

        if ($courseIds) {
            $query->whereIn('id', $courseIds);
        }

        return $query->get();
    }

    /**
     * Return a specific course while checking the users access rights.
     *
     * @param User $user
     * @param $courseId
     * @return Course|null
     */
    public function getUsersCourse(User $user, $courseId)
    {
        return $this
            ->getUsersCourses($user, [$courseId], true)
            ->first();
    }

    /**
     * Returns the last participation of the user.
     *
     * @param Course $course
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\HasMany|CourseParticipation|null
     */
    public function getLastParticipation(Course $course, User $user)
    {
        return $course
            ->participations()
            ->where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->first();
    }

    /**
     * Creates a new course participation
     *
     * @param Course $course
     * @param User $user
     * @return CourseParticipation
     */
    public function createParticipation(Course $course, User $user): CourseParticipation
    {
        $participation = new CourseParticipation();
        $participation->user_id = $user->id;
        $participation->course_id = $course->id;
        $participation->save();
        AnalyticsEvent::log($user, AnalyticsEvent::TYPE_COURSE_START, $course);
        return $participation;
    }

    /**
     * Loads the relatable relation for course contents which have relatable content.
     *
     * @param Course $course
     */
    public function loadRelatable(Course $course)
    {
        // Load translation relations for contents which have a translation
        $withRelatableRelations = $course->chapters->reduce(function (Collection $carry, CourseChapter $chapter) {
            return $carry->concat($chapter->contents->whereIn('type', [CourseContent::TYPE_LEARNINGMATERIAL]));
        }, new Collection());

        $withRelatableRelations->load([
            'relatable',
        ]);
    }

    /**
     * Loads the attachments relation for course contents which have any.
     *
     * @param Course $course
     */
    public function loadAttachments(Course $course)
    {
        // Load translation relations for question types
        $questionContents = $course->chapters->reduce(function (Collection $carry, CourseChapter $chapter) {
            return $carry->concat($chapter->contents->whereIn('type', [CourseContent::TYPE_QUESTIONS]));
        }, new Collection());

        $questionContents->load([
            'attachments.attachment',
        ]);

        /** @var TranslationEngine $translationEngine */
        $translationEngine = app(TranslationEngine::class);
        /** @var QuestionsEngine $questionsEngine */
        $questionsEngine = app(QuestionsEngine::class);

        /** @var \Illuminate\Support\Collection $questions */
        $questions = $questionContents->reduce(function (Collection $carry, CourseContent $content) {
            return $carry->concat($content->attachments->pluck('attachment'));
        }, new Collection())->filter();
        if ($questions->count()) {
            $questions->load('category.translationRelation');
            $questions->keyBy('id');
            $questions = $translationEngine->attachQuestionTranslations($questions, $course->app);
            $translationEngine->attachQuestionAttachments($questions);
            $translationEngine->attachQuestionAnswers($questions);
            $questions->transform(function ($question) use ($questionsEngine) {
                $answers = collect($question->answers)
                    ->map(function ($a) {
                        return [
                            'id' => $a->id,
                            'content' => $a->content,
                        ];
                    })->values();

                $questionsEngine->formatQuestionForFrontend($question, $question->app_id);

                $attachments = isset($question->attachments) ? $question->attachments : [];

                return [
                    'id' => $question->id,
                    'type' => $question->type,
                    'latex' => $question->latex,
                    'category' => $question->category ? $question->category->name : '',
                    'category_parent' => ($question->category && $question->category->categorygroup_id) ? $question->category->categorygroup->name : null,
                    'category_image' => $question->category ? formatAssetURL($question->category->image_url) : null,
                    'title' => $question->title,
                    'answers' => $answers,
                    'attachments' => $attachments,
                    'answertime' => $question->answertime,
                ];
            });
            $questions->keyBy('id');
            $questionContents->map(function (CourseContent $content) use ($questions) {
                $content->attachments->transform(function (CourseContentAttachment $attachment) use ($questions) {
                    $attachment->question = $questions->get($attachment->foreign_id);
                    unset($attachment->attachment);

                    return $attachment;
                });
            });
        }
    }

    /**
     * Loads the translation of the relatable relation for course contents which have relatable translations.
     *
     * @param Course $course
     */
    public function loadRelatableTranslations(Course $course)
    {
        // Load translation relations for contents which have a translation
        $withTranslatedRelations = $course->chapters->reduce(function (Collection $carry, CourseChapter $chapter) {
            return $carry->concat($chapter->contents->whereIn('type', [CourseContent::TYPE_LEARNINGMATERIAL]));
        }, new Collection());

        $relations = [
            'relatable.learningMaterialFolder.translationRelation',
            'relatable.translationRelation',
        ];

        if (language($course->app_id) != defaultAppLanguage($course->app_id)) {
            $relations[] = 'relatable.learningMaterialFolder.defaultTranslationRelation';
            $relations[] = 'relatable.defaultTranslationRelation';
        }

        $withTranslatedRelations->load($relations);
    }

    public function attachUserParticipations($courses, User $user)
    {
        $userTags = $user->tags->pluck('id');

        $mostRecentParticipations = CourseParticipation
            ::where('user_id', $user->id)
            ->whereIn('course_id', $courses->pluck('id'))
            ->orderBy('course_participations.id', 'DESC')
            ->groupBy('course_id')
            ->get()
            ->keyBy('course_id');
        $passedParticipationAttemptsQuery = CourseContentAttempt
            ::whereIn('course_participation_id', $mostRecentParticipations->pluck('id'))
            ->where('passed', true)
            ->whereHas('content', function ($query) use ($userTags) {
                $query->where(function ($query) use ($userTags) {
                    $query->doesntHave('tags')
                        ->orWhereHas('tags', function ($query) use ($userTags) {
                            $query->whereIn('tags.id', $userTags);
                        });
                });
            })
            ->groupBy('course_content_id');
        $passedParticipationAttemptCounts = DB::query()
            ->fromSub($passedParticipationAttemptsQuery, 'counts')
            ->select([DB::raw('counts.course_participation_id as id'), DB::raw('COUNT(*) as c')])
            ->groupBy('counts.course_participation_id')
            ->pluck('c', 'id');

        $courses->transform(function (Course $course) use ($mostRecentParticipations, $passedParticipationAttemptCounts) {
            $course->userParticipation = $mostRecentParticipations->get($course->id, null);
            if ($course->userParticipation) {
                $course->passed_contents = $passedParticipationAttemptCounts->get($course->userParticipation->id, 0);
            } else {
                $course->passed_contents = 0;
            }

            return $course;
        });
    }

    /**
     * Gets a list of the finished participations
     * of the given users for the given courses
     *
     * @param Collection $courses
     * @param Collection $users
     * @return Collection
     */
    public function getUserFinishedCourseParticipations(Collection $courses, Collection $users): Collection
    {
        $latestParticipationsSubquery = DB::table('course_participations')
            ->select([DB::raw('MAX(id) as latest_id'), 'course_id', 'user_id'])
            ->whereIn('course_id', $courses->pluck('id'))
            ->whereIn('user_id', $users->pluck('id'))
            ->groupBy(['course_id', 'user_id']);
        return CourseParticipation::selectRaw('*')
            ->joinSub($latestParticipationsSubquery, 'latest_participations', function ($join) {
                $join->on('course_participations.id', '=', 'latest_participations.latest_id');
            })
            ->whereNotNull('finished_at')
            ->get();
    }

    /**
     * Attaches the expected total course durations for a user to a collection of courses
     *
     * @param Collection $courses
     * @param User $user
     * @return Collection
     */
    public function attachCourseDurations(Collection $courses, User $user): Collection
    {
        $durations = $this->getCourseDurations($courses, $user);
        $courses->transform(function (Course $course) use ($durations) {
            $course->total_duration = intval($durations->get($course->id, 0));

            return $course;
        });

        return $courses;
    }

    private function getCourseDurations(Collection $courses, User $user):\Illuminate\Support\Collection
    {
        $userTags = $user->tags->pluck('id');

        return CourseContent::leftJoin('course_chapters', 'course_chapters.id', '=', 'course_contents.course_chapter_id')
            ->groupBy('course_chapters.course_id')
            ->select([DB::raw('course_chapters.course_id as course_id'), DB::raw('SUM(course_contents.duration) as total_duration')])
            ->where('course_contents.visible', 1)
            ->whereIn('course_chapters.course_id', $courses->pluck('id'))
            ->where(function ($query) use ($userTags) {
                return $query
                    ->where(function ($query) use ($userTags) {
                        $query
                            ->selectRaw('COUNT(*)')
                            ->from('course_content_tag', 'cct')
                            ->whereIn('cct.tag_id', $userTags)
                            ->whereColumn('cct.course_content_id', 'course_contents.id');
                    }, '>', 0)
                    ->orWhere(function ($query) {
                        $query
                            ->selectRaw('COUNT(*)')
                            ->from('course_content_tag', 'cct')
                            ->whereColumn('cct.course_content_id', 'course_contents.id');
                    }, 0);
            })
            ->get()
            ->pluck('total_duration', 'course_id');
    }

    /**
     * Gets the duration for the given courses and users
     *
     * @param Collection $courses
     * @param Collection $users
     * @return Collection
     */
    public function getUsersCourseDurations(Collection $courses, Collection $users): \Illuminate\Support\Collection
    {
        $userCourseDurations = collect();
        $cachedDurations = [];
        foreach ($users as $user) {
            $tags = $user->tags->pluck('id')->sort()->join('-');
            if (!isset($cachedDurations[$tags])) {
                $cachedDurations[$tags] = $this->getCourseDurations($courses, $user);
            }
            $userCourseDurations->push([
                'user_id' => $user->id,
                'durations' => $cachedDurations[$tags],
            ]);
        }
        return $userCourseDurations;
    }

    public function courseFilterQuery($appId, $search = null, $tags = null, $filter = null, $categories = null, $orderBy = null, $descending = false, $ignoreTagRights = false, $isTemplate = false)
    {
        /** @var Builder $coursesQuery */
        $coursesQuery = Course::where('app_id', $appId);
        if(!$ignoreTagRights) {
            $coursesQuery = $coursesQuery->tagRights();
        }

        if ($search) {
            $matchingTitles = DB::table('course_translations')
                ->join('courses', 'course_translations.course_id', '=', 'courses.id')
                ->select('courses.id')
                ->where('courses.app_id', $appId)
                ->whereRaw('course_translations.title LIKE ?', '%'.escapeLikeInput($search).'%');
            $coursesQuery->where(function ($query) use ($search, $matchingTitles) {
                $query->whereIn('id', $matchingTitles)
                    ->orWhere('id', extractHashtagNumber($search));
            });
        }

        // Filters for courses
        if(!$isTemplate) {
            switch ($filter) {
                case 'active':
                    $coursesQuery
                        ->whereNull('archived_at')
                        ->where(function ($query) {
                            $query->where('duration_type', Course::DURATION_TYPE_DYNAMIC)
                                ->orWhere(function ($query) {
                                    $query->where('duration_type', Course::DURATION_TYPE_FIXED)
                                        ->where(function ($query) {
                                            $query->where('visible', 1)
                                                ->orWhere(function($query) {
                                                    $query
                                                        ->whereNull('available_from')
                                                        ->orWhere('available_from', '>', Carbon::now()->startOfDay());
                                                });
                                        })
                                        ->where(function ($query) {
                                            $query->whereNull('available_until')
                                                ->orWhere('available_until', '>=', Carbon::now()->startOfDay());
                                        });
                                });
                        });
                    break;
                case 'expired':
                    $coursesQuery
                        ->where('duration_type', Course::DURATION_TYPE_FIXED)
                        ->where(function ($query) {
                            $query->where(function ($query) {
                                $query
                                    ->whereNull('archived_at')
                                    ->whereNotNull('available_until')
                                    ->Where('available_until', '<', Carbon::now()->startOfDay());
                            })
                            ->orWhere(function ($query) {
                                $query
                                    ->whereNull('archived_at')
                                    ->where('visible', 0)
                                    ->where('available_from', '<=', Carbon::now()->startOfDay());
                            });
                        });

                    break;
                case 'archived':
                    $coursesQuery->whereNotNull('archived_at');
                    break;
            }
        }

        // Filters for course templates
        if($isTemplate) {
            switch ($filter) {
                case 'visible':
                    $coursesQuery
                        ->where('visible', 1)
                        ->whereNull('archived_at');
                    break;
                case 'invisible':
                    $coursesQuery
                        ->where('visible', 0)
                        ->whereNull('archived_at');
                    break;
                case 'is_repeating':
                    $coursesQuery
                        ->where('is_repeating', 1)
                        ->whereNull('archived_at');
                    break;
                case 'is_not_repeating':
                    $coursesQuery
                        ->where('is_repeating', 0)
                        ->whereNull('archived_at');
                    break;
                case 'archived':
                    $coursesQuery->whereNotNull('archived_at');
                    break;
            }
            $coursesQuery = $coursesQuery->template();
        }

        if ($tags) {
            $addCoursesWithoutTag = in_array(-1, $tags);
            $tags = array_filter($tags, function ($tag) {
                return $tag !== '-1';
            });
            if (count($tags)) {
                $coursesQuery->where(function (Builder $query) use ($tags, $addCoursesWithoutTag) {
                    $query->whereHas('tags', function ($query) use ($tags) {
                        $query->whereIn('tags.id', $tags);
                    });
                    if ($addCoursesWithoutTag) {
                        $query->orWhereDoesntHave('tags');
                    }
                });
            } else {
                $coursesQuery->doesntHave('tags');
            }
        }

        if ($categories && count($categories)) {
            $coursesQuery->where(function (Builder $query) use ($categories) {
                $query->whereHas('categories', function ($query) use ($categories) {
                    $query->whereIn('content_categories.id', $categories);
                });
            });
        }

        if ($orderBy) {
            if ($orderBy == 'title') {
                $coursesQuery = Course::orderByTranslatedField($coursesQuery, 'title', $appId, $descending ? 'desc' : 'asc');
            } else {
                $coursesQuery->orderBy($orderBy, $descending ? 'desc' : 'asc');
            }
        }

        return $coursesQuery;
    }

    public function hasUnpassedContents(CourseParticipation $participation, $contentIds)
    {
        return $participation
                ->contentAttempts()
                ->whereIn('course_content_id', $contentIds)
                ->where('passed', true)
                ->groupBy('course_content_id')
                ->pluck('course_content_id')
                ->count() < count($contentIds);
    }

    public function hasPassedPreviousContent(Course $course, CourseParticipation $participation, $contentId, User $user)
    {
        /** @var CourseContent $content */
        $content = CourseContent::find($contentId);
        if ($content->chapter->course_id !== $course->id) {
            return false;
        }
        if ($participation->course_id !== $course->id) {
            return false;
        }

        $userTags = $user->tags()->pluck('tags.id');

        $previousContentIds = CourseContent
            ::where('visible', 1)
            ->where(function ($query) use ($userTags) {
                $query->where(function ($query) use ($userTags) {
                    $query->doesntHave('tags')
                        ->orWhereHas('tags', function ($query) use ($userTags) {
                            $query->whereIn('tags.id', $userTags);
                        });
                });
            })
            ->leftJoin('course_chapters', 'course_contents.course_chapter_id', '=', 'course_chapters.id')
            ->where('course_chapters.course_id', $course->id)
            ->where(function (Builder $q) use ($content) {
                $q->where('course_chapters.position', '<', $content->chapter->position)
                ->orWhere(function (Builder $q) use ($content) {
                    $q->where('course_contents.course_chapter_id', $content->course_chapter_id)
                      ->where('course_contents.position', '<', $content->position);
                });
            })
            ->orderBy('course_chapters.position')
            ->orderBy('course_contents.position')
            ->pluck('course_contents.id');

        if ($previousContentIds->isEmpty()) {
            return true;
        }

        return ! $this->hasUnpassedContents($participation, [$previousContentIds->last()]);
    }

    public function hasPassedAllContents(Course $course, CourseParticipation $participation, User $user)
    {
        if ($participation->course_id !== $course->id) {
            return false;
        }

        $userTags = $user->tags()->pluck('tags.id');

        $contentIds = $course
            ->contents()
            ->where('visible', 1)
            ->where(function ($query) use ($userTags) {
                $query->where(function ($query) use ($userTags) {
                    $query->doesntHave('tags')
                        ->orWhereHas('tags', function ($query) use ($userTags) {
                            $query->whereIn('tags.id', $userTags);
                        });
                });
            })
            ->pluck('course_contents.id');

        return ! $this->hasUnpassedContents($participation, $contentIds);
    }

    /**
     * Returns null if the attempt is not complete yet, otherwise a boolean if the attempt was passed.
     *
     * @param CourseContentAttempt $attempt
     * @param CourseContent $content
     * @return bool|null Returns null when the attempt is not completed yet, otherwise a boolean if the attempt was passed
     */
    public function getAttemptStatus(CourseContentAttempt $attempt, CourseContent $content): ?bool
    {
        if ($content->type === CourseContent::TYPE_TODOLIST) {
            $hasUnfinishedTodoItem = TodolistItem::where('todolist_items.todolist_id', $content->foreign_id)
                ->leftJoin('todolist_item_answers', function($join) use ($attempt) {
                    $join->on('todolist_items.id', '=', 'todolist_item_answers.todolist_item_id')
                        ->where('todolist_item_answers.user_id', '=', $attempt->participation->user_id);
                })
                ->where(function($query) {
                    $query->where('todolist_item_answers.is_done', 0)
                    ->orWhereNull('todolist_item_answers.is_done');
                })
                ->exists();
            return $hasUnfinishedTodoItem ? null : true;
        }
        $passedCount = 0;
        $completedCount = 0;
        foreach ($attempt->attachments as $courseContentAttemptAttachment) {
            $completedCount++;
            if ($courseContentAttemptAttachment->passed) {
                $passedCount++;
            }
        }
        if ($completedCount < $content->attachments->count()) {
            // We haven't answered all questions yet
            return null;
        }
        if ($content->type === CourseContent::TYPE_QUESTIONS && $content->is_test) {
            // We answered all questions, return if enough were correct
            $passedPercentage = $passedCount / $content->attachments->count();

            return $passedPercentage >= ($content->pass_percentage / 100);
        } else {
            // We answered all questions, return true
            return true;
        }
    }

    public function markAttemptAsPassed(CourseContentAttempt $attempt, Course $course, CourseParticipation $participation, User $user)
    {
        if ($attempt->passed === null) {
            $attempt->passed = true;
            $attempt->finished_at = Carbon::now();
            $attempt->append('certificate_download_url');
            $attempt->save();
            $participation->touch();
        }
        $this->checkParticipationStatus($course, $participation, $user);
    }

    /**
     * Checks if a participation is passed,
     * updating it if so.
     *
     * @param Course $course
     * @param CourseParticipation $participation
     * @param User $user
     * @return void
     */
    public function checkParticipationStatus(Course $course, CourseParticipation $participation, User $user): void
    {
        if ($participation->passed !== null) {
            return;
        }
        if (!$this->hasPassedAllContents($course, $participation, $user)) {
            return;
        }
        $participation->passed = true;
        $participation->finished_at = Carbon::now();
        $participation->save();
        $this->updateUserTags($course, $user);

        AnalyticsEvent::log($user, AnalyticsEvent::TYPE_COURSE_SUCCESS, $course);

        if($course->send_passed_course_mail) {
            $mailer = app(Mailer::class);
            $mailer->sendPassedCourse($user, $course, $participation);
        }
    }

    public function markAttemptAsFailed(CourseContentAttempt $attempt, Course $course, CourseParticipation $participation, CourseContent $content, User $user)
    {
        if ($attempt->passed === null) {
            $attempt->passed = false;
            $attempt->finished_at = Carbon::now();
            $attempt->append('certificate_download_url');
            $attempt->save();
            $participation->touch();
        }
        if (! $this->canRepeatContent($participation, $content)) {
            if($participation->passed === null) {
                $participation->passed = false;
                $participation->finished_at = Carbon::now();
                $participation->save();
            }
        }
    }

    public function canRepeatContent(CourseParticipation $participation, CourseContent $content)
    {
        if (! $content->isRepeatable()) {
            return false;
        }

        if (! $content->isEndlesslyRepeatable()) {
            $currentAttemptCount = CourseContentAttempt
                ::where('course_participation_id', $participation->id)
                ->where('course_content_id', $content->id)
                ->count();
            if ($currentAttemptCount >= $content->repetitions) {
                return false;
            }
        }

        return true;
    }

    public function requestAccess(Course $course, User $user)
    {
        try {
            $request = new CourseAccessRequest();
            $request->user_id = $user->id;
            $request->course_id = $course->id;
            $request->save();
        } catch (\Exception $e) {
            report($e);
        }

        /** @var Mailer $mailer */
        $mailer = app(Mailer::class);
        $mailer->sendCourseAccessRequest($course, $user);
    }

    /**
     * Checks if the user has permissions to the course content.
     *
     * @param User $user
     * @param CourseContent $courseContent
     * @return bool
     */
    public function hasPermissionForCourseContent(User $user, CourseContent $courseContent) {
        $userTags = $user->tags->pluck('id');
        $contentTags = $courseContent->tags->pluck('id');
        $allowedTags = $contentTags->intersect($userTags);

        if($allowedTags->isEmpty() && $contentTags->isNotEmpty()) {
            return false;
        }
        if($contentTags->isNotEmpty() && $userTags->isEmpty()) {
            return false;
        }

        return true;
    }

    /**
     * Checks if the admin has permissions to the course content.
     *
     * @param User $user
     * @param CourseContent $courseContent
     * @return bool
     */
    public function hasAdminPermissionForCourseContent(User $user, CourseContent $courseContent) {

        if($user->isFullAdmin()) {
            return true;
        }

        $contentTags = $courseContent->tags->pluck('id');
        $tagRights = $user->tagRightsRelation->pluck('id');
        $allowedTagRights = $contentTags->intersect($tagRights);

        if($allowedTagRights->isEmpty() && $contentTags->isNotEmpty()) {
            return false;
        }

        return true;
    }

    /**
     * Returns new potential participants for the course.
     *
     * @param Course $course
     * @return Builder[]|Collection|\Illuminate\Database\Query\Builder[]|\Illuminate\Support\Collection
     */
    public function newPotentialParticipants(Course $course) {
        $courseStatisticsEngine = app(CourseStatisticsEngine::class);

        return $courseStatisticsEngine
            ->getCourseEligibleUsersQuery($course)
            ->where('email', '!=', '')
            ->whereNotNull('email')
            ->whereNotIn('users.id', $course->participations->pluck('user_id'))
            ->get();
    }

    /**
     * Notifies users about the new  available course.
     *
     * @param Course $course
     */
    public function notifyAboutNewCourse(Course $course) {
        $mailer = app(Mailer::class);

        $potentialParticipants = $this->newPotentialParticipants($course);

        foreach($potentialParticipants as $user) {
            $mailer->sendNewCourseNotification($user, $course);
        }
    }

    public function updateParticipationFailureState(CourseParticipation $participation, CourseContent $content) {

        if ($content->type !== CourseContent::TYPE_QUESTIONS || !$content->is_test) {
            return;
        }

        $hasPassedContent = CourseContentAttempt
            ::where('course_participation_id', $participation->id)
            ->where('course_content_id', $content->id)
            ->where('passed', 1)
            ->exists();

        // If a user passed the test in the past, don't set the participation as failed
        if($hasPassedContent) {
            return;
        }

        $lastAttempt = $participation
            ->contentAttempts()
            ->where('course_content_id', $content->id)
            ->orderByDesc('updated_at')
            ->first();

        // the last attempt has to be failed
        if(!$lastAttempt || $lastAttempt->passed !== 0) {
            return;
        }

        if($this->canRepeatContent($participation, $content)) {
            return;
        }

        $participation->passed = 0;
        $participation->finished_at = Carbon::now();
        $participation->save();
    }

    private function updateUserTags($course, User $user) {
        $awardTags = $course->awardTags->pluck('id');
        $retractTags = $course->retractTags->pluck('id');

        DB::transaction(function () use ($user, $retractTags, $awardTags) {
            $userTagRelationship = $user->tags();

            $userTagRelationship->detach($retractTags);

            // Instead of calling $userTagRelationship->syncWithoutDetaching, we're doing it manually, so that we can use `insertOrIgnore`
            // because we sometimes call this code in very quick succession and that way we make sure not to throw any errors.
            $currentUserTags = $userTagRelationship->allRelatedIds();
            foreach ($awardTags as $tagId) {
                if (!$currentUserTags->contains($tagId)) {
                    // This code is based on the $tagRelationship's `attach` method and just changed so that it uses `insertOrIgnore` instead
                    $now = Date::now();
                    $record = [
                        'tag_id' => $tagId,
                        'user_id' => $user->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $userTagRelationship->newPivotStatement()->insertOrIgnore($record);
                }
            }
        });
    }
}
