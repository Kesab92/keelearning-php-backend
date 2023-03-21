<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Http\Requests\BackendApi\Course\CourseContentStoreRequest;
use App\Models\AccessLog;
use App\Models\Appointments\Appointment;
use App\Models\Courses\CourseChapter;
use App\Models\Courses\CourseContent;
use App\Models\Courses\CourseContentAttachment;
use App\Models\Question;
use App\Services\AccessLogEngine;
use App\Services\AccessLogMeta\Courses\AccessLogCourseChapterDelete;
use App\Services\Appointments\AppointmentEngine;
use App\Services\Courses\CourseContentsEngine;
use App\Services\Courses\CoursesEngine;
use App\Transformers\BackendApi\Courses\SimpleCourseContentWithSimpleCourseTransformer;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Response;

class CourseContentsController extends Controller
{
    /**
     * @var CoursesEngine
     */
    private CoursesEngine $coursesEngine;
    /**
     * @var CourseContentsEngine
     */
    private CourseContentsEngine $courseContentsEngine;

    public function __construct(CoursesEngine $coursesEngine, CourseContentsEngine $courseContentsEngine)
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:courses,courses-edit|courses-view');
        $this->middleware('auth.backendaccess:courses,courses-edit')->only([
            'chapterDeleteInformation',
            'create',
            'delete',
            'deleteChapter',
            'deleteInformation',
            'persistQuestionAttachments',
            'update',
            'updateChapter',
            'updateContentPositions',
        ]);
        $this->coursesEngine = $coursesEngine;
        $this->courseContentsEngine = $courseContentsEngine;
    }

    public function create($courseId, CourseContentStoreRequest $request)
    {
        $course = $this->coursesEngine->getCourse($courseId, \Auth::user());
        $type = intval($request->input('type'));
        $position = intval($request->input('position'));
        $chapterId = $request->input('chapter');
        if (! $chapterId || !in_array($type, CourseContent::TYPES)) {
            app()->abort(400);
        }

        if ($type === CourseContent::TYPE_CHAPTER) {
            $content = $this->courseContentsEngine->createChapter($course, $chapterId);
        } else {
            $content = $this->courseContentsEngine->createContent($course, $type, $chapterId, $position);
        }

        return Response::json($content);
    }

    // FIXME: gets called twice for some reason?
    public function show($courseId, $contentId)
    {
        $course = $this->coursesEngine->getCourse($courseId, \Auth::user());

        // This returns an array specific to the content type
        $contentData = $this->courseContentsEngine->getContent($course, $contentId);

        $contentData['content']->translations = $contentData['content']->allTranslationRelations->whereIn('language', [defaultAppLanguage(), language()])->values();
        $contentData['content']->unsetRelation('allTranslationRelations');

        if ($contentData['content']['type'] === CourseContent::TYPE_LEARNINGMATERIAL) {
            $contentData['availableLearningmaterials'] = $this->courseContentsEngine->getAvailableLearningmaterials($course, language());

            if($contentData['learningmaterial']) {
                $contentData['learningmaterial']->translations = $contentData['learningmaterial']->allTranslationRelations->whereIn('language', [defaultAppLanguage(), language()])->values();
                $contentData['learningmaterial']->unsetRelation('allTranslationRelations');
            }
        }

        $tagsIds = $contentData['content']->tags->pluck('id');
        $contentData['content'] = $contentData['content']->toArray();
        $contentData['content']['tags'] = $tagsIds;

        return Response::json($contentData);
    }

    public function updateChapter($courseId, $chapterId, Request $request)
    {
        $course = $this->coursesEngine->getCourse($courseId, \Auth::user());
        $chapter = $course->chapters()->where('id', $chapterId)->first();
        if (! $chapter) {
            app()->abort(403);
        }
        $chapter->title = $request->input('title');
        $chapter->save();

        return Response::json([], 204);
    }

    public function deleteInformation($courseId, $contentId) {
        $course = $this->coursesEngine->getCourse($courseId, \Auth::user());
        $contentData = $this->courseContentsEngine->getContent($course, $contentId);
        /** @var CourseContent $content */
        $content = $contentData['content'];

        return Response::json([
            'dependencies' => $content->safeRemoveDependees(),
            'blockers' => $content->getBlockingDependees(),
        ]);
    }

    public function delete($courseId, $contentId)
    {
        $course = $this->coursesEngine->getCourse($courseId, \Auth::user());
        $contentData = $this->courseContentsEngine->getContent($course, $contentId);
        /** @var CourseContent $content */
        $content = $contentData['content'];
        $content->safeRemove();

        return Response::json([], 204);
    }

    public function update($courseId, $contentId, Request $request, AppointmentEngine $appointmentEngine)
    {
        $course = $this->coursesEngine->getCourse($courseId, \Auth::user());
        $contentData = $this->courseContentsEngine->getContent($course, $contentId);
        $content = $contentData['content'];

        $content->title = $request->input('title');
        $content->description = $request->input('description');
        $content->visible = $request->input('visible', false);
        $content->foreign_id = $request->input('foreign_id', null);
        $duration = $request->input('duration', null);
        if ($duration !== null) {
            $content->duration = $duration;
        }

        if ($content->needsForeignObject() && $content->visible && ! $content->foreign_id) {
            app()->abort(400);
        }

        if ($content->foreign_id) {
            if ($content->type === CourseContent::TYPE_LEARNINGMATERIAL) {
                $availableLearningmaterials = $this->courseContentsEngine->getAvailableLearningmaterials($course, language());
                if (! $availableLearningmaterials->contains('id', $content->foreign_id)) {
                    app()->abort(403);
                }
            }
            if($content->type === CourseContent::TYPE_APPOINTMENT) {
                $appointment = Appointment::findOrFail($content->foreign_id);
                $appointmentEngine->updateDurationForCourseContents($appointment, [$content->id]);
            }
        }
        if ($content->type === CourseContent::TYPE_QUESTIONS) {
            $content->is_test = $request->input('is_test', false);
            $content->pass_percentage = $request->input('pass_percentage', null);
            $content->repetitions = $request->input('repetitions', 1);
            $content->show_correct_result = $request->input('show_correct_result', true);
            $this->persistQuestionAttachments($content, collect($request->input('attachments', [])));
        }
        $content->save();

        if($request->has('tags')) {
            $content->syncTags($request->input('tags', []), 'tags', true);
        }

        return Response::json([], 204);
    }

    public function updateChapterPositions($courseId, Request $request)
    {
        $course = $this->coursesEngine->getCourse($courseId, \Auth::user());
        $chapterUpdates = collect($request->input('chapters', []))->keyBy('id');
        $chapterIds = $chapterUpdates->pluck('id');
        $chapters = CourseChapter::whereIn('id', $chapterIds)->get();
        foreach ($chapters as $chapter) {
            if ($chapter->course_id !== $course->id) {
                continue;
            }
            $chapterUpdate = $chapterUpdates->get($chapter->id);
            $chapter->position = $chapterUpdate['position'];
            $chapter->save();
        }
        return Response::json([], 204);
    }

    public function updateContentPositions($courseId, Request $request)
    {
        $course = $this->coursesEngine->getCourse($courseId, \Auth::user());
        $contentUpdates = collect($request->input('contents', []))
            ->keyBy('id');
        $contentIds = $contentUpdates->pluck('id');
        /** @var CourseContent[] $contents */
        $contents = $course->chapters->reduce(function (Collection $carry, CourseChapter $chapter) use ($contentIds) {
            return $carry->concat($chapter->contents->whereIn('id', $contentIds));
        }, new Collection());
        foreach ($contents as $content) {
            $contentUpdate = $contentUpdates->get($content->id);
            if (! $contentUpdate) {
                continue;
            }
            $content->position = $contentUpdate['position'];
            $content->course_chapter_id = $contentUpdate['course_chapter_id'];
            if ($content->isDirty('course_chapter_id')) {
                // Check if we are allowed to set this chapter id
                $chapter = CourseChapter::find($content->course_chapter_id);
                if ($chapter->course_id !== $course->id) {
                    continue;
                }
            }
            $content->save();
        }

        return Response::json([], 204);
    }

    /**
     * Returns course contents for the form.
     *
     * @param SimpleCourseContentWithSimpleCourseTransformer $simpleCourseContentWithSimpleCourseTransformer
     * @return JsonResponse
     */
    public function getFormCourseContents(int $formId, SimpleCourseContentWithSimpleCourseTransformer $simpleCourseContentWithSimpleCourseTransformer): JsonResponse
    {
        $courseContents = CourseContent
            ::where('type', CourseContent::TYPE_FORM)
            ->where('foreign_id', $formId)
            ->with([
                'course.translationRelation',
                'translationRelation',
            ])
            ->whereHas('course', function ($query) {
                $query->where('app_id', appId())
                    ->tagRights();
            })
            ->get();

        return response()->json([
            'courseContents' => $simpleCourseContentWithSimpleCourseTransformer->transformAll($courseContents),
        ]);
    }

    private function persistQuestionAttachments(CourseContent $content, Collection $attachments)
    {
        $attachmentIds = $attachments->unique('question.id')->pluck('id');

        // Remove unused attachments
        // It's important that we do this first, because otherwise we would also delete the attachments which we just created
        $content
            ->attachments()
            ->whereNotIn('id', $attachmentIds)
            ->delete();

        foreach ($attachments as $attachmentData) {
            $attachment = $content->attachments->find($attachmentData['id']);
            if (! $attachment) {
                $question = Question
                    ::where('id', $attachmentData['question']['id'])
                    ->where('app_id', $content->chapter->course->app_id)
                    ->first();
                if (! $question) {
                    continue;
                }

                $attachment = new CourseContentAttachment();
                $attachment->course_content_id = $content->id;
                $attachment->foreign_id = $question->id;
                $attachment->type = CourseContentAttachment::TYPE_QUESTION;
            }
            $attachment->position = $attachmentData['position'];
            $attachment->save();
        }
    }

    public function chapterDeleteInformation($courseId, $chapterId) {
        $course = $this->coursesEngine->getCourse($courseId, \Auth::user());
        $chapter = $course->chapters()->where('id', $chapterId)->first();
        if(!$chapter) {
            app()->abort(403);
        }

        return Response::json([
            'dependencies' => $chapter->safeRemoveDependees(),
            'blockers' => $chapter->getBlockingDependees(),
        ]);
    }

    public function deleteChapter($courseId, $chapterId, AccessLogEngine $accessLogEngine) {
        $course = $this->coursesEngine->getCourse($courseId, \Auth::user());
        $chapter = $course->chapters()->where('id', $chapterId)->first();
        if(!$chapter) {
            app()->abort(403);
        }
        $result = DB::transaction(function() use ($accessLogEngine, $chapter) {
            $accessLogEngine->log(AccessLog::ACTION_DELETE_COURSE_CHAPTER, new AccessLogCourseChapterDelete($chapter), \Auth::user()->id);
            return $chapter->safeRemove();
        });

        if($result->isSuccessful()) {
            return Response::json([], 204);
        } else {
            return Response::json($result->getMessages(), 400);
        }
    }
}
