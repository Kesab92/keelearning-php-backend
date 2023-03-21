<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Models\Courses\CourseContent;
use App\Models\Courses\CourseContentAttempt;
use App\Models\Courses\CourseParticipation;
use App\Models\Todolist;
use App\Models\TodolistItemAnswer;
use App\Models\User;
use App\Services\CourseCertificateRenderer;
use App\Services\Courses\CoursesEngine;
use App\Traits\PersonalData;
use DB;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CourseParticipationsController extends Controller
{
    use PersonalData;

    /**
     * @var CoursesEngine
     */
    private CoursesEngine $coursesEngine;

    public function __construct(CoursesEngine $coursesEngine)
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:courses,courses-edit|courses-view');
        $this->middleware('auth.backendaccess:courses,courses-edit')->only(['markAsNotFinished']);
        $this->personalDataRightsMiddleware('courses');
        $this->coursesEngine = $coursesEngine;
    }

    /**
     * @param int $courseId
     * @param int $participationId
     * @param int $attemptId
     * @return StreamedResponse|void
     */
    public function certificate(int $courseId, int $participationId, int $attemptId)
    {
        if (!$this->showPersonalData) {
            app()->abort(403);
        }

        $course = $this->coursesEngine->getCourse($courseId, Auth::user());
        $participation = CourseParticipation::findOrFail($participationId);

        if ($participation->course_id !== $course->id) {
            app()->abort(404);
        }

        $attempt = CourseContentAttempt::findOrFail($attemptId);

        $content = $attempt->content;

        if ($attempt->course_participation_id !== $participation->id) {
            app()->abort(404);
        }

        if(!$this->coursesEngine->hasAdminPermissionForCourseContent(Auth::user(), $content)) {
            app()->abort(403);
        }

        $lang = $participation->user->getLanguage();

        $courseCertificateRenderer = new CourseCertificateRenderer($content, $participation, $attempt, $lang);

        $name = 'User ' . $participation->user->id;
        if ($this->showPersonalData) {
            $name = $participation->user->getDisplayNameBackend();
        }
        $filename = $participation->course->title . ' - ' . $name . ' - ' . $attempt->created_at->format('d.m.Y');

        return $courseCertificateRenderer->render($filename);
    }

    public function markAsNotFinished(int $courseId, int $participationId)
    {
        $course = $this->coursesEngine->getCourse($courseId, Auth::user());

        $participation = CourseParticipation::findOrFail($participationId);

        if($participation->passed != 0) {
            app()->abort(403);
        }

        if($participation->course_id !== $course->id) {
            app()->abort(403);
        }

        $containsTest = $participation->course->contents->contains(function($courseContent) {
            return $courseContent->is_test;
        });

        if(!$containsTest) {
            app()->abort(403);
        }

        $participation->passed = null;
        $participation->save();

        return response()->json([]);
    }

    /**
     * @param int $courseId
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function getParticipants(int $courseId): JsonResponse
    {
        $this->coursesEngine->getCourse($courseId, Auth::user());

        $users = User::activeOfApp(appId())
            ->select(['users.*', DB::raw('course_participations.id as participation_id')])
            ->join('course_participations', 'course_participations.user_id', '=', 'users.id')
            ->tagRights()
            ->where('course_participations.course_id', $courseId)
            ->get()
            ->map(function(User $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->getDisplayNameBackend($this->showEmails) . ' (#' . $user->id . ')',
                    'participation_id' => $user->participation_id,
                ];
            });

        return response()->json(['participations' => $users]);
    }

    public function getTodolistStatus(int $courseId, int $participationId, int $contentId): JsonResponse
    {
        $course = $this->coursesEngine->getCourse($courseId, Auth::user());
        /** @var CourseParticipation $participation */
        $participation = $course->participations()->where('id',$participationId)->first();
        if(!$participation->user->isAccessibleByAdmin()) {
            app()->abort(403);
        }
        /** @var CourseContent $content */
        $content = $course->contents()->where('course_contents.id', $contentId)->firstOrFail();
        if($content->type !== CourseContent::TYPE_TODOLIST || !$content->foreign_id) {
            app()->abort(404);
        }
        $todolist = Todolist::where('app_id', $course->app_id)
            ->where('id', $content->foreign_id)
            ->with('todolistItems.translationRelation')
            ->first();
        return response()->json([
            'todolist' => $todolist,
            'answers' => TodolistItemAnswer::where('user_id', $participation->user_id)->whereIn('todolist_item_id', $todolist->todolistItems()->pluck('id'))->get(),
            'attempt' => $participation->contentAttempts()->where('course_content_id', $contentId)->first(),
        ]);
    }
}
