<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Models\Reminder;
use App\Services\CourseReminderEngine;
use App\Services\Courses\CoursesEngine;
use App\Services\MorphTypes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Response;

class CourseRemindersController extends Controller
{
    /**
     * @var CoursesEngine
     */
    private CoursesEngine $coursesEngine;

    public function __construct(CoursesEngine $coursesEngine)
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:courses,courses-edit|courses-view');
        $this->middleware('auth.backendaccess:courses,courses-edit')->only([
            'delete',
            'store',
        ]);
        $this->coursesEngine = $coursesEngine;
    }

    /**
     * Gets reminders of the course.
     * @param int $courseId
     * @return JsonResponse
     */
    public function index(int $courseId):JsonResponse {
        $this->coursesEngine->getCourse($courseId, Auth::user());

        return Response::json(['reminders' => $this->getReminders($courseId)]);
    }

    /**
     * Stores a reminder of the course.
     * @param int $courseId
     * @param Request $request
     * @param CourseReminderEngine $courseReminderEngine
     * @return JsonResponse
     */
    public function store(int $courseId, Request $request, CourseReminderEngine  $courseReminderEngine):JsonResponse
    {
        $user = Auth::user();
        $this->coursesEngine->getCourse($courseId, $user);

        if(!in_array($request->input('type'), Reminder::TYPES[MorphTypes::TYPE_COURSE])) {
            abort(403);
        }

        if($request->input('type') === Reminder::TYPE_ADMIN_COURSE_NOTIFICATION && !$request->has('emails')) {
            abort(403);
        }

        $reminder = new Reminder();
        $reminder->app_id = appId();
        $reminder->user_id = $user->id;
        $reminder->foreign_id = $courseId;
        $reminder->type = $request->input('type');
        $reminder->days_offset = $request->input('days_offset');
        $reminder->save();

        if($request->has('emails') && $request->input('type') === Reminder::TYPE_ADMIN_COURSE_NOTIFICATION) {
            $emails = parseEmails($request->input('emails'));
            $courseReminderEngine->updateEmails($courseId, $emails);
        }

        return Response::json(['reminders' => $this->getReminders($courseId)]);
    }

    /**
     * Deletes the reminder.
     * @param int $courseId
     * @param int $reminderId
     * @return JsonResponse
     * @throws \Exception
     */
    public function delete(int $courseId, int $reminderId):JsonResponse
    {
        $this->coursesEngine->getCourse($courseId, Auth::user());

        $reminder = Reminder::findOrFail($reminderId);

        $result = $reminder->safeRemove();
        if($result->isSuccessful()) {
            return Response::json(['reminders' => $this->getReminders($courseId)]);
        } else {
            return Response::json($result->getMessages(), 400);
        }
    }

    /**
     * Returns reminders of the course.
     *
     * @param $courseId
     * @return Reminder[]
     */
    private function getReminders(int $courseId) {
        return Reminder
            ::where('foreign_id', $courseId)
            ->whereIn('type', Reminder::TYPES[MorphTypes::TYPE_COURSE])
            ->with('metadata')
            ->get();
    }
}
