<?php

namespace App\Http\Controllers\Api;

use App\Http\APIError;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Form\FormAnswerStoreRequest;
use App\Models\Courses\Course;
use App\Models\Courses\CourseContent;
use App\Models\Courses\CourseContentAttachment;
use App\Models\Courses\CourseContentAttempt;
use App\Models\Courses\CourseContentAttemptAttachment;
use App\Models\Courses\CourseParticipation;
use App\Models\NotificationSubscription;
use App\Models\Question;
use App\Models\User;
use App\Services\CourseCertificateRenderer;
use App\Services\Courses\CoursesEngine;
use App\Services\Forms\FormEngine;
use App\Services\GameEngine;
use App\Services\MorphTypes;
use DB;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Response;
use Throwable;

class CourseParticipationsController extends Controller
{
    /**
     * Creates a new participation, or returns the most recent one.
     *
     * @return JsonResponse
     */
    public function create($courseId, CoursesEngine $coursesEngine)
    {
        $user = user();
        $course = $coursesEngine->getUsersCourse($user, $courseId);
        if (! $course) {
            app()->abort(404);
        }
        if($course->isPreview($user)) {
            app()->abort(403);
        }
        $participation = $coursesEngine->getLastParticipation($course, $user);
        if (! $participation) {
            $participation = $coursesEngine->createParticipation($course, $user);
        }

        return Response::json($participation);
    }

    public function show($courseId, $participationId, CoursesEngine $coursesEngine)
    {
        $user = user();
        $course = $coursesEngine->getUsersCourse($user, $courseId);

        if (! $course) {
            app()->abort(404);
        }

        $participation = $course
            ->participations()
            ->where('user_id', $user->id)
            ->where('id', $participationId)
            ->first();

        if (! $participation) {
            app()->abort(404);
        }

        $participation->load([
            'contentAttempts.attachments',
            'contentAttempts.content',
        ]);

        // we have to check the course participation here,
        // in case content-in-progress got deleted
        $coursesEngine->checkParticipationStatus($course, $participation, $user);

        $participation->contentAttempts->each(function (CourseContentAttempt $attempt) {
            $attempt->append('certificate_download_url');
        })->transform(function (CourseContentAttempt $attempt) use ($participation) {
            return $this->formatAttempt($attempt, $participation);
        });

        return Response::json($participation);
    }

    private function formatAttempt(CourseContentAttempt $attempt, CourseParticipation $participation)
    {
        return [
            'id' => $attempt->id,
            'attachments' => $attempt->attachments,
            'certificate_download_url' => $attempt->certificate_download_url,
            'passed' => $attempt->passed,
            'finished_at' => $attempt->finished_at,
            'created_at' => $attempt->created_at,
            'updated_at' => $attempt->updated_at,
            'course_content_id' => $attempt->course_content_id,
            'course_participation_id' => $attempt->course_participation_id,
            'course_participation' => [
                'course_id' => $participation->course_id,
                'created_at' => $participation->created_at,
                'finished_at' => $participation->finished_at,
                'id' => $participation->id,
                'passed' => $participation->passed,
                'updated_at' => $participation->updated_at,
                'user_id' => $participation->user_id,
            ],
        ];
    }

    public function markContentAsPassed($courseId, $participationId, $contentId, CoursesEngine $coursesEngine)
    {
        $user = user();
        $course = $coursesEngine->getUsersCourse($user, $courseId);

        if (! $course) {
            app()->abort(404);
        }

        /** @var CourseParticipation $participation */
        $participation = $course
            ->participations()
            ->where('user_id', $user->id)
            ->where('id', $participationId)
            ->first();

        if (! $participation) {
            app()->abort(404);
        }

        /** @var CourseContent $content */
        $content = $coursesEngine->getCourseContent($course, $contentId, $user);

        if (! $content) {
            app()->abort(404);
        }

        if (! $coursesEngine->hasPassedPreviousContent($course, $participation, $contentId, $user)) {
            app()->abort(403);
        }

        $response = $this->syncCourseContentAttempt($participation, $content, $course);

        return Response::json($response);
    }

    public function answerQuestion($courseId, $participationId, int $contentId, $questionId, Request $request, CoursesEngine $coursesEngine, GameEngine $gameEngine)
    {
        $user = user();
        $course = $coursesEngine->getUsersCourse($user, $courseId);

        if (! $course) {
            app()->abort(404);
        }

        /** @var CourseParticipation $participation */
        $participation = $course
            ->participations()
            ->where('user_id', $user->id)
            ->where('id', $participationId)
            ->first();

        if (! $participation) {
            app()->abort(404);
        }

        $content = $coursesEngine->getCourseContent($course, $contentId, $user);

        if (! $content) {
            app()->abort(404);
        }

        /** @var CourseContentAttachment $attachment */
        $attachment = $content
            ->attachments
            ->where('type', MorphTypes::TYPE_QUESTION)
            ->where('foreign_id', $questionId)
            ->first();

        if (! $attachment) {
            app()->abort(404);
        }

        /** @var Question $question */
        $question = $attachment->attachment;

        if (! $question) {
            app()->abort(404);
        }

        if (! $coursesEngine->hasPassedPreviousContent($course, $participation, $contentId, $user)) {
            app()->abort(403);
        }

        $questionAnswerId = $request->input('question_answer_id');

        $response = $gameEngine->getAnswerResponse($question, $questionAnswerId);
        if ($response) {
            \DB::transaction(function () use ($user, &$response, $question, $questionAnswerId, $participation, $contentId, $attachment, $content, $course, $coursesEngine) {
                $isCorrect = $question->isCorrect($questionAnswerId);
                if ($content->show_correct_result) {
                    $response['result'] = $isCorrect;
                } else {
                    $response['result'] = null;
                    $response['feedback'] = null;
                    $response['correct_answer_id'] = null;
                }

                $attempt = $participation
                    ->contentAttempts()
                    ->where('course_content_id', $contentId)
                    ->orderBy('id', 'desc')
                    ->first();
                if (! $attempt) {
                    $attempt = new CourseContentAttempt();
                    $attempt->course_content_id = $contentId;
                    $attempt->course_participation_id = $participation->id;
                    $attempt->save();
                }
                if ($attempt->attachments()->where('course_content_attachment_id', $attachment->id)->count() === 0) {
                    $attemptAttachment = new CourseContentAttemptAttachment();
                    $attemptAttachment->course_content_attachment_id = $attachment->id;
                    $attemptAttachment->value = is_array($questionAnswerId) ? implode(',', $questionAnswerId) : $questionAnswerId;
                    $attemptAttachment->passed = $isCorrect;
                    $attempt->attachments()->save($attemptAttachment);
                }
                $attempt->load('attachments');

                $attemptStatus = $coursesEngine->getAttemptStatus($attempt, $content);
                if ($attemptStatus !== null) {
                    if ($attemptStatus === true) {
                        $coursesEngine->markAttemptAsPassed($attempt, $course, $participation, $user);
                    }
                    if ($attemptStatus === false) {
                        $coursesEngine->markAttemptAsFailed($attempt, $course, $participation, $content, $user);
                    }
                }

                $response['attempt'] = $this->formatAttempt($attempt, $participation);
            });

            return Response::json($response);
        }

        // No correct answer found?
        return new APIError(__('errors.generic'));
    }

    public function getCorrectAnswers($courseId, $participationId, $contentId, $questionId, Request $request, CoursesEngine $coursesEngine, GameEngine $gameEngine)
    {
        $user = user();
        $course = $coursesEngine->getUsersCourse($user, $courseId);

        if (! $course) {
            app()->abort(404);
        }

        /** @var CourseParticipation $participation */
        $participation = $course
            ->participations()
            ->where('user_id', $user->id)
            ->where('id', $participationId)
            ->first();

        if (! $participation) {
            app()->abort(404);
        }

        /** @var CourseContent $content */
        $content = $coursesEngine->getCourseContent($course, $contentId, $user);

        if (! $content) {
            app()->abort(404);
        }

        if (! $content->show_correct_result) {
            return Response::json([]);
        }

        /** @var CourseContentAttachment $attachment */
        $attachment = $content
            ->attachments
            ->where('type', MorphTypes::TYPE_QUESTION)
            ->where('foreign_id', $questionId)
            ->first();

        if (! $attachment) {
            app()->abort(404);
        }

        /** @var Question $question */
        $question = $attachment->attachment;

        if (! $question) {
            app()->abort(404);
        }

        /** @var CourseContentAttempt $attempt */
        $attempt = $participation
            ->contentAttempts()
            ->where('course_content_id', $contentId)
            ->orderBy('id', 'desc')
            ->first();
        if (! $attempt) {
            app()->abort(403);
        }
        $hasAnsweredQuestion = $attempt
            ->attachments()
            ->where('course_content_attachment_id', $attachment->id)
            ->count() > 0;

        if (! $hasAnsweredQuestion) {
            app()->abort(403);
        }

        $correctAnswers = $question
            ->questionAnswers()
            ->where('correct', 1)
            ->pluck('question_answers.id');

        return Response::json($correctAnswers);
    }

    public function certificate($courseId, $participationId, $attemptId, CoursesEngine $coursesEngine)
    {

        $attempt = CourseContentAttempt::findOrFail($attemptId);

        $content = $attempt->content;
        $participation = CourseParticipation::findOrFail($participationId);

        if ($attempt->course_participation_id !== $participation->id) {
            app()->abort(404);
        }

        $user = $participation->user;

        if(!$coursesEngine->hasPermissionForCourseContent($user, $content)) {
            app()->abort(404);
        }

        $lang = $participation->user->getLanguage();

        $courseCertificateRenderer = new CourseCertificateRenderer($content, $participation, $attempt, $lang);

        $filename = $participation->course->title . ' - ' . $participation->user->username . ' - ' . $attempt->created_at->format('d.m.Y');
        return $courseCertificateRenderer->render($filename);
    }

    /**
     * Starts a new attempt for the given content.
     *
     * @param $courseId
     * @param $participationId
     * @param $contentId
     * @param CoursesEngine $coursesEngine
     * @return JsonResponse
     */
    public function repeatAttempt($courseId, $participationId, $contentId, CoursesEngine $coursesEngine)
    {
        $user = user();
        $course = $coursesEngine->getUsersCourse($user, $courseId);

        if (! $course) {
            app()->abort(404);
        }

        /** @var CourseParticipation $participation */
        $participation = $course
            ->participations()
            ->where('user_id', $user->id)
            ->where('id', $participationId)
            ->first();

        if (! $participation) {
            app()->abort(404);
        }

        /** @var CourseContent $content */
        $content = $coursesEngine->getCourseContent($course, $contentId, $user);

        if (! $content) {
            app()->abort(404);
        }

        if (! $coursesEngine->canRepeatContent($participation, $content)) {
            app()->abort(400);
        }

        $attempt = new CourseContentAttempt();
        $attempt->course_content_id = $contentId;
        $attempt->course_participation_id = $participation->id;
        $attempt->save();

        return Response::json($this->formatAttempt($attempt, $participation));
    }

    /**
     * Saves the form answer.
     *
     * @param int $courseId
     * @param int $participationId
     * @param int $contentId
     * @param int $formId
     * @param FormAnswerStoreRequest $request
     * @param CoursesEngine $coursesEngine
     * @param FormEngine $formEngine
     * @return JsonResponse
     * @throws Throwable
     */
    public function submitForm(int $courseId, int $participationId, int $contentId, int $formId, FormAnswerStoreRequest $request, CoursesEngine $coursesEngine, FormEngine $formEngine)
    {
        $user = user();
        $course = $coursesEngine->getUsersCourse($user, $courseId);

        if (!$course) {
            app()->abort(404);
        }

        /** @var CourseParticipation $participation */
        $participation = $course
            ->participations()
            ->where('user_id', $user->id)
            ->where('id', $participationId)
            ->first();

        if (! $participation) {
            app()->abort(404);
        }

        $content = $coursesEngine->getCourseContent($course, $contentId, $user);

        if (!$content) {
            app()->abort(404);
        }

        if (!$coursesEngine->hasPassedPreviousContent($course, $participation, $contentId, $user)) {
            app()->abort(403);
        }

        $answerFields = $request->input('answerFields');

        $response = DB::transaction(function () use ($formEngine, $content, $course, $answerFields, $user, $formId, $participation) {
            $response = [];
            $response['attempt'] = $this->syncCourseContentAttempt($participation, $content, $course);
            $formEngine->saveFormAnswer($formId, $user, $answerFields, MorphTypes::TYPE_COURSE_CONTENT_ATTEMPT, $response['attempt']['id']);

            return $response;
        });

        return Response::json($response);
    }

    /**
     * syncs and returns the course content attempt
     * @param CourseParticipation $participation
     * @param CourseContent $content
     * @param Course $course
     * @return array
     * @throws Exception
     */
    private function syncCourseContentAttempt(CourseParticipation $participation,  CourseContent $content, Course $course):array {
        $coursesEngine = app(CoursesEngine::class);

        $attempt = $participation
            ->contentAttempts()
            ->where('course_content_id', $content->id)
            ->orderBy('id', 'desc')
            ->first();

        $created = false;
        if (!$attempt) {
            $created = true;
            $attempt = new CourseContentAttempt();
            $attempt->course_content_id = $content->id;
            $attempt->course_participation_id = $participation->id;
        }

        $attemptStatus = $coursesEngine->getAttemptStatus($attempt, $content);
        if ($attemptStatus !== null) {
            if ($attemptStatus === true) {
                $coursesEngine->markAttemptAsPassed($attempt, $course, $participation, user());
            }
            if ($attemptStatus === false) {
                $coursesEngine->markAttemptAsFailed($attempt, $course, $participation, $content, user());
            }
        } else {
            $attempt->save();
        }
        if ($created) {
            // subscribe course moderators to the comment section
            if ($content->type == CourseContent::TYPE_TODOLIST) {
                NotificationSubscription::subscribe(user()->id, MorphTypes::TYPE_COURSE_CONTENT_ATTEMPT, $attempt->id);
                $managers = $course->managers;
                if ($managers->isEmpty()) {
                    $managers = User::ofApp($course->app_id)
                        ->where('is_admin', 1)
                        ->whereHas('role', function ($roleQuery) {
                            $roleQuery->where('is_main_admin', 1);
                        })
                        ->get();
                }
                foreach ($managers as $manager) {
                    NotificationSubscription::subscribe($manager->id, MorphTypes::TYPE_COURSE_CONTENT_ATTEMPT, $attempt->id);
                }
            }
        }

        return $this->formatAttempt($attempt, $participation);
    }
}
