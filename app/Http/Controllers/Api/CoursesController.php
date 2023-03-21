<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointments\Appointment;
use App\Models\AzureVideo;
use App\Models\ContentCategories\ContentCategory;
use App\Models\Courses\Course;
use App\Models\Courses\CourseChapter;
use App\Models\Courses\CourseContent;
use App\Models\Forms\Form;
use App\Models\LearningMaterial;
use App\Models\Like;
use App\Services\AzureVideo\AzureVideoEngine;
use App\Models\Todolist;
use App\Services\CommentEngine;
use App\Services\Courses\CoursesEngine;
use App\Services\LikesEngine;
use App\Services\MorphTypes;
use App\Services\TranslationEngine;
use App\Services\WbtEngine;
use App\Transformers\Api\Courses\Contents\AppointmentContentTransformer;
use App\Transformers\Api\Courses\Contents\FormContentTransformer;
use App\Transformers\Api\Todolists\TodolistTransformer;
use Exception;
use Illuminate\Http\JsonResponse;
use Response;

class CoursesController extends Controller
{
    /**
     * Returns a list of all courses.
     *
     * @return JsonResponse
     */
    public function courses(CoursesEngine $coursesEngine, LikesEngine $likesEngine, CommentEngine $commentEngine, TranslationEngine $translationEngine)
    {
        $user = user();
        $userTags = $user->tags()->pluck('tags.id');

        $courses = $coursesEngine->getUsersCourses($user);
        $courses->load([
            'translationRelation',
            'categories.translationRelation',
            'earliestReminder',
            'individualAttendees',
            'tags',
            'visibleContents',
            'visibleContents.tags',
            'visibleContents.translationRelation',
        ]);

        $coursesEngine->attachUserParticipations($courses, $user);
        $coursesEngine->attachCourseDurations($courses, $user);

        $likesCount = $likesEngine->getLikesCounts($courses);
        $userLikes = $likesEngine->getUserLikes($courses, $user);

        $commentsCount = $commentEngine->getCommentsCount($courses, $user);
        $courses->transform(function (Course $course) use ($translationEngine, $coursesEngine, $commentsCount, $userTags, $likesCount, $userLikes, $user) {
            $visibleContents = $course->visibleContents()
                ->where(function ($query) use ($userTags) {
                    $query->doesntHave('tags')
                        ->orWhereHas('tags', function ($query) use ($userTags) {
                            $query->whereIn('tags.id', $userTags);
                        });
                });

            $availabilityDates = $this->getAvailabilityDates($course);

            $translationEngine->cacheTranslations($course->visibleContents);

            return [
                'id' => $course->id,
                'title' => $course->title,
                'description' => $course->description,
                'duration_type' => $course->duration_type,
                'available_from' => $availabilityDates['availableFrom'],
                'available_until' => $availabilityDates['availableTo'],
                'is_mandatory' => $course->is_mandatory,
                'participation' => $course->userParticipation ?: null,
                'participation_duration' => $course->participation_duration,
                'participation_duration_type' => $course->participation_duration_type,
                'passed_contents' => $course->passed_contents,
                'contents_count' => $visibleContents->count(),
                'total_duration' => $course->total_duration,
                'cover_image_url' => $course->cover_image_url,
                'likes_count'     => $likesCount->get($course->id, 0),
                'likes_it'        => $userLikes->contains($course->id),
                'comment_count'     => $commentsCount->get($course->id, 0),
                'earliest_reminder' => $course->earliestReminder ? $course->earliestReminder->days_offset : null,
                'preview' => $course->isPreview($user),
                'categories'      => $course->categories->map(function(ContentCategory $category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                    ];
                }),
                'contents' => $course->visibleContents->filter(function (CourseContent $content) use ($coursesEngine, $user) {
                    if (!$coursesEngine->hasPermissionForCourseContent($user, $content)) {
                        return false;
                    }
                    return true;
                })->map(function (CourseContent $content) {
                    return [
                        'id' => $content->id,
                        'title' => $content->title,
                    ];
                })->values(),
            ];
        });

        return Response::json($courses);
    }

    /**
     * @param Course $course
     * @return array{availableFrom: string, availableTo: string}
     */
    private function getAvailabilityDates(Course $course)
    {
        $availableFrom = $course->available_from ? $course->available_from->format('Y-m-d H:i:s') : null;
        $availableTo = null;

        if ($course->duration_type == Course::DURATION_TYPE_FIXED) {
            $availableTo = $course->available_until ? $course->available_until->format('Y-m-d H:i:s') : null;
        }
        if ($course->duration_type == Course::DURATION_TYPE_DYNAMIC && $course->userParticipation) {
            $availableFrom = $course->userParticipation->created_at->format('Y-m-d H:i:s');
            $availableTo = $course->userParticipation->availableUntil()->format('Y-m-d H:i:s');
        }
        return [
            'availableFrom' => $availableFrom,
            'availableTo' => $availableTo,
        ];
    }

    public function courseContents($courseId, CoursesEngine $coursesEngine, LikesEngine $likesEngine)
    {
        $user = user();
        $course = $coursesEngine->getUsersCourse($user, $courseId);
        if (! $course) {
            app()->abort(404);
        }

        $courseContentsWithRelatable = [
            CourseContent::TYPE_APPOINTMENT,
            CourseContent::TYPE_FORM,
            CourseContent::TYPE_LEARNINGMATERIAL,
            CourseContent::TYPE_TODOLIST,
        ];

        $likesEngine = app(LikesEngine::class);

        $course->load([
            'chapters.contents.translationRelation',
            'chapters.contents.tags',
            'chapters.translationRelation',
            'tags',
            'categories.translationRelation',
        ]);

        $coursesEngine->loadRelatable($course);
        $coursesEngine->loadRelatableTranslations($course);
        $coursesEngine->loadAttachments($course);
        $likeCount = $likesEngine->likesCount(Like::TYPE_COURSES, $course->id);

        $lastParticipation = $coursesEngine->getLastParticipation($course, $user);

        $availabilityDates = $this->getAvailabilityDates($course);

        foreach ($course->chapters as $chapter) {
            foreach ($chapter->contents as $content) {
                if (!$content->visible) {
                    continue;
                }

                if (!$coursesEngine->hasPermissionForCourseContent($user, $content)) {
                    continue;
                }

                if($lastParticipation) {
                    $coursesEngine->updateParticipationFailureState($lastParticipation, $content);
                }
            }
        }

        $data = [
            'course' => [
                'id' => $course->id,
                'available_from' => $availabilityDates['availableFrom'],
                'available_until' => $availabilityDates['availableTo'],
                'duration_type' => $course->duration_type,
                'title' => $course->title,
                'description' => $course->description,
                'cover_image_url' => $course->cover_image_url,
                'is_mandatory' => $course->is_mandatory,
                'participation' => $lastParticipation,
                'participation_duration' => $course->participation_duration,
                'participation_duration_type' => $course->participation_duration_type,
                'likes_count' => $likeCount,
                'managers' => $course->managers->pluck('id'),
                'preview' => $course->isPreview($user),
                'request_access_link' => $course->request_access_link,
                'track_wbts' => $course->track_wbts,
                'categories' => $course->categories->map(function(ContentCategory $category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                    ];
                }),
                'chapters' => $course->chapters->transform(function (CourseChapter $chapter) use ($courseContentsWithRelatable, $likesEngine, $coursesEngine, $user, $course) {
                    return [
                        'id' => $chapter->id,
                        'title' => $chapter->title,
                        'position' => $chapter->position,
                        'contents' => $chapter->contents->transform(function (CourseContent $content) use ($courseContentsWithRelatable, $likesEngine, $coursesEngine, $user, $course) {
                            if (! $content->visible) {
                                return null;
                            }

                            if(!$coursesEngine->hasPermissionForCourseContent($user, $content)) {
                                return null;
                            }

                            $title = $content->title;
                            if (!$title && in_array($content->type, $courseContentsWithRelatable) && $content->relatable) {
                                switch ($content->type) {
                                    case CourseContent::TYPE_LEARNINGMATERIAL:
                                    case CourseContent::TYPE_FORM:
                                        $title = $content->relatable->title;
                                        break;
                                    case CourseContent::TYPE_APPOINTMENT:
                                        $title = $content->relatable->name;
                                        break;
                                }
                            }
                            $description = $content->description;
                            if (! $description && $content->type === CourseContent::TYPE_LEARNINGMATERIAL && $content->relatable) {
                                $description = $content->relatable->description;
                            }
                            $relatable = null;
                            if ($content->foreign_id) {
                                $relatable = $this->formatRelatableData($content, $course, $likesEngine);
                            }
                            $attachments = $content->attachments;

                            return [
                                'id' => $content->id,
                                'title' => $title,
                                'type' => $content->type,
                                'description' => $description,
                                'relatable' => $relatable,
                                'attachments' => $attachments,
                                'course_chapter_id' => $content->course_chapter_id,
                                'pass_percentage' => $content->pass_percentage,
                                'duration' => $content->duration,
                                'is_test' => $content->is_test,
                                'repetitions' => $content->repetitions,
                                'show_correct_result' => $content->show_correct_result,
                            ];
                        })->filter()->values(),
                    ];
                })->filter(function ($chapter) {
                    return count($chapter['contents']) > 0;
                })->values(),
            ],
        ];

        if ($user->is_admin) {
            $data['course']['is_template'] = $course->is_template;
            $data['course']['visible'] = $course->visible;
            $data['course']['archived_at'] = $course->archived_at ? $course->archived_at->toDateTimeString() : null;
        }

        return Response::json($data);
    }

    public function courseContentWbtEvents($courseId, $contentId, CoursesEngine $coursesEngine)
    {
        $user = user();
        $course = $coursesEngine->getUsersCourse($user, $courseId);
        if (!$course) {
            app()->abort(404);
        }
        $courseContent = $coursesEngine->getCourseContent($course, $contentId, $user);
        if (!$courseContent) {
            app()->abort(404);
        }
        if(!$user->app->hasXAPI()) {
            return Response::json([
                'events' => [],
            ]);
        }
        $wbtEngine = new WbtEngine($user->app_id);
        $events = $wbtEngine->getUserCourseContentEvents($user, $courseContent);

        // Old frontends expect only the "closing" events
        if(!hasAPIVersion('3.2.0')) {
            $validEventStatuses = array_map(function($status) {
                return WbtEngine::VERB_TO_STATUS[$status];
            }, WbtEngine::CLOSING_EVENT_VERBS);
            $events = $events->filter(function ($event) use ($validEventStatuses) {
                return in_array($event['status'], $validEventStatuses);
            });
        }
        return Response::json([
            'events' => $events->values(),
        ]);
    }

    private function formatRelatableData(CourseContent $content, Course $course, LikesEngine $likesEngine)
    {
        if ($content->relatable instanceof LearningMaterial) {
            return $this->formatLearningMaterialData($content, $course, $likesEngine);
        }
        if ($content->relatable instanceof Form) {
            $content->load('relatable.fields.translationRelation');
            $formContentTransformer = app(FormContentTransformer::class);
            return $formContentTransformer->transform($content);
        }
        if ($content->relatable instanceof Appointment) {
            $appointmentContentTransformer = app(AppointmentContentTransformer::class);
            return $appointmentContentTransformer->transform($content);
        }
        if ($content->relatable instanceof Todolist) {
            $content->load('relatable.todolistItems.translationRelation');
            return app(TodolistTransformer::class)->transform($content->relatable);
        }
        return null;
    }

    private function formatLearningMaterialData(CourseContent $content, Course $course, LikesEngine $likesEngine) {
        /** @var LearningMaterial $learningMaterial */
        $learningMaterial = $content->relatable;

        $folder = [];
        if ($learningMaterial->learningMaterialFolder) {
            $folder = [
                'id' => $learningMaterial->learningMaterialFolder->id,
                'folder_icon_url' => $learningMaterial->learningMaterialFolder->folder_icon_url,
                'name' => $learningMaterial->learningMaterialFolder->name,
            ];
        }
        $fileUrl = $learningMaterial->file_url;
        $subtitles = [];
        if ($learningMaterial->file_type !== 'wbt' && $learningMaterial->file_type !== 'azure_video') {
            $fileUrl = formatAssetURL($learningMaterial->file_url);
        }
        if ($learningMaterial->file_type === 'azure_video') {
            $azureVideo = AzureVideo::where('app_id', $course->app_id)
                ->where('id', $learningMaterial->file)
                ->first();
            if ($azureVideo) {
                $azureVideoEngine = app(AzureVideoEngine::class);
                $activeSubtitles =  $azureVideoEngine->getActiveSubtitles([$azureVideo->id]);

                $fileUrl = $azureVideo->streaming_url;
                $subtitles = $activeSubtitles->map(function ($activeSubtitle) {
                    return [
                        'language' => $activeSubtitle->language,
                        'streaming_url' => $activeSubtitle->streaming_url,
                    ];
                });
            } else {
                $fileUrl = '';
            }
        }

        $likeCount = $likesEngine->likesCount(Like::TYPE_LEARNINGMATERIAL, $learningMaterial->id);

        return [
            'id' => $learningMaterial->id,
            'cover_image_url' => $learningMaterial->cover_image_url,
            'description' => $content->description ?: $learningMaterial->description,
            'download_disabled' => $learningMaterial->download_disabled,
            'file_size_kb' => $learningMaterial->file_size_kb,
            'file_type' => $learningMaterial->file_type,
            'file_url' => $fileUrl,
            'link' => $learningMaterial->link,
            'subtitles' => $subtitles,
            'title' => $content->title ?: $learningMaterial->title,
            'wbt_id' => $learningMaterial->wbt_id,
            'learning_material_folder' => $folder,
            'show_watermark' => $learningMaterial->show_watermark,
            'watermark' => $learningMaterial->watermark,
            'wbt_subtype' => $learningMaterial->wbt_subtype,
            'wbt_custom_entrypoint' => $learningMaterial->wbt_custom_entrypoint,
            'likes_count' => $likeCount,
        ];
    }
}
