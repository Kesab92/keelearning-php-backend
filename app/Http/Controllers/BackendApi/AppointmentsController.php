<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Http\Requests\BackendApi\Appointment\AppointmentStoreRequest;
use App\Http\Requests\BackendApi\Appointment\AppointmentUpdateRequest;
use App\Jobs\SendAppointmentPublishNotifications;
use App\Jobs\SendAppointmentUpdateNotifications;
use App\Mail\AppointmentStartDateWasUpdated;
use App\Models\AccessLog;
use App\Mail\Mailer;
use App\Models\Appointments\Appointment;
use App\Services\AccessLogEngine;
use App\Services\AccessLogMeta\Appointments\AccessLogAppointmentDelete;
use App\Services\AccessLogMeta\Appointments\AccessLogAppointmentCreate;
use App\Services\Appointments\AppointmentEngine;
use App\Services\ImageUploader;
use App\Transformers\BackendApi\Appointments\SimpleAppointmentTransformer;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AppointmentsController
    extends Controller
{
    const ORDER_BY = [
        'id',
        'name',
        'type',
        'start_date',
    ];
    const PER_PAGE = [
        50,
        100,
        200,
    ];

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:appointments,appointments-edit|appointments-view')->except('getAll');
        $this->middleware('auth.backendaccess:,appointments-edit|appointments-view|courses-edit|courses-view')->only('getAll');

        $this->middleware('auth.backendaccess:appointments,appointments-edit')->only([
            'cover',
            'delete',
            'deleteInformation',
            'notify',
            'store',
            'update',
        ]);
    }

    /**
     * Returns appointments data
     *
     * @param Request $request
     * @param AppointmentEngine $appointmentEngine
     * @return JsonResponse
     * @throws \Exception
     */
    public function index(Request $request, AppointmentEngine $appointmentEngine):JsonResponse
    {
        $orderBy = $request->input('sortBy');
        if (! in_array($orderBy, self::ORDER_BY)) {
            $orderBy = self::ORDER_BY[0];
        }
        $orderDescending = $request->input('descending') === 'true';
        $page = (int) $request->input('page') ?? 1;
        $perPage = $request->input('rowsPerPage');
        if (! in_array($perPage, self::PER_PAGE)) {
            $perPage = self::PER_PAGE[0];
        }
        $filter = $request->input('filter');
        $search = utrim($request->input('search'));
        $tags = $request->input('tags', []);

        $appointmentQuery = $appointmentEngine->appointmentsFilterQuery(appId(), Auth::user(), $search, $tags, $filter, $orderBy, $orderDescending);

        $count = $appointmentQuery->count();
        $appointments = $appointmentQuery
            ->with([
                'translationRelation',
                'tags',
            ])
            ->offset($perPage * ($page - 1))
            ->limit($perPage)
            ->get();

        if($appointments->count()) {
            $appointmentEngine->attachParticipantCount($appointments, appId());
        }

        return response()->json([
            'count' => $count,
            'appointments' => $this->formatAppointments($appointments)->values(),
        ]);
    }

    /**
     * Adds the appointment
     *
     * @param AppointmentStoreRequest $request
     * @param AccessLogEngine $accessLogEngine
     * @return JsonResponse
     * @throws \Throwable
     */
    public function store(AppointmentStoreRequest $request, AccessLogEngine $accessLogEngine):JsonResponse
    {
        $appointment = DB::transaction(function() use ($accessLogEngine, $request) {
            $appointment = new Appointment();
            $appointment->app_id = appId();
            $appointment->setLanguage(defaultAppLanguage(appId()));
            $appointment->created_by_id = Auth::user()->id;
            $appointment->last_updated_by_id = Auth::user()->id;
            $appointment->type = Appointment::TYPE_IN_PERSON;
            $appointment->name = $request->input('name');
            $appointment->start_date = $request->input('start_date');
            $appointment->end_date = $request->input('end_date');
            $appointment->save();
            $appointment->syncTags($request->input('tags', []));

            $accessLogEngine->log(AccessLog::ACTION_APPOINTMENT_CREATE, new AccessLogAppointmentCreate($appointment));

            return $appointment;
        });

        return response()->json([
            'appointment' => $this->getAppointmentResponse($appointment),
        ]);
    }

    /**
     * Returns the appointment using JSON
     *
     * @param int $appointmentId
     * @return JsonResponse
     * @throws \Exception
     */
    public function show(int $appointmentId):JsonResponse {
        $appointment = $this->getAppointment($appointmentId);

        return response()->json([
            'appointment' => $this->getAppointmentResponse($appointment),
        ]);
    }

    /**
     * Updates the cover image.
     *
     * @param int $appointmentId
     * @param Request $request
     * @param ImageUploader $imageUploader
     * @return JsonResponse
     * @throws \Exception
     */
    public function cover(int $appointmentId, Request $request, ImageUploader $imageUploader): JsonResponse
    {
        $this->getAppointment($appointmentId);

        $file = $request->file('file');
        if (! $imageUploader->validate($file)) {
            app()->abort(403);
        }

        if (! $imagePath = $imageUploader->upload($file, 'uploads/appointment-cover')) {
            app()->abort(400);
        }
        $imagePath = formatAssetURL($imagePath, '3.0.0');

        return response()->json([
            'image' => $imagePath,
        ]);
    }

    /**
     * Updates the appointment
     *
     * @param int $appointmentId
     * @param AppointmentUpdateRequest $request
     * @param AppointmentEngine $appointmentEngine
     * @param Mailer $mailer
     * @return JsonResponse
     * @throws \Throwable
     */
    public function update(int $appointmentId, AppointmentUpdateRequest $request, AppointmentEngine $appointmentEngine,Mailer $mailer): JsonResponse
    {
        $appointment = DB::transaction(function() use ($appointmentEngine, $mailer, $appointmentId, $request) {
            $appointment = $this->getAppointment($appointmentId);

            $basicFields = [
                'name',
                'description',
                'is_draft',
                'type',
                'cover_image_url',
                'has_reminder',
                'reminder_time',
                'reminder_unit_type',
                'location',
                'send_notification',
            ];

            foreach ($basicFields as $field) {
                if ($request->has($field)) {
                    $value = $request->input($field, null);
                    $appointment->setAttribute($field, $value);
                }
            }

            $appointment->last_updated_by_id = Auth::user()->id;

            if($request->has('start_date')) {
                if ($request->input('start_date')) {
                    $appointment->start_date = Carbon::parse($request->input('start_date'));
                } else {
                    $appointment->start_date = null;
                }
            }

            if($request->has('end_date')) {
                if ($request->input('end_date')) {
                    $appointment->end_date = Carbon::parse($request->input('end_date'));
                } else {
                    $appointment->end_date = null;
                }
            }

            if($request->has('published_at')) {
                if ($request->input('published_at')) {
                    $appointment->published_at = Carbon::parse($request->input('published_at'));
                } else {
                    $appointment->published_at = null;
                }
            }

            if($appointment->has_reminder && $appointment->start_date->isFuture()) {
                $sendReminderAt = $appointment->start_date->clone();

                switch ($appointment->reminder_unit_type) {
                    case Appointment::REMINDER_TIME_UNIT_MINUTES:
                        $sendReminderAt->subMinutes($appointment->reminder_time);
                        break;
                    case Appointment::REMINDER_TIME_UNIT_HOURS:
                        $sendReminderAt->subHours($appointment->reminder_time);
                        break;
                    case Appointment::REMINDER_TIME_UNIT_DAYS:
                        $sendReminderAt->subDays($appointment->reminder_time);
                        break;
                }

                if(
                    $appointment->isDirty('start_date') ||
                    $appointment->isDirty('reminder_time') ||
                    $appointment->isDirty('reminder_unit_type')
                ) {
                    $appointment->send_reminder_at = $sendReminderAt;
                }
            } else {
                $appointment->send_reminder_at = null;
            }

            // The notification should be sent when the appointment is published, and the start date of an already published appointment is changed.
            if(
                !$appointment->is_draft &&
                !$appointment->isDirty('is_draft') &&
                !$appointment->is_cancelled &&
                $appointment->isDirty('start_date')
            ) {
                SendAppointmentUpdateNotifications::dispatch($appointment, AppointmentStartDateWasUpdated::START_DATE_WAS_UPDATED);
            }

            $appointment->save();

            if($request->has('tags')) {
                $appointment->syncTags($request->input('tags', []), 'tags', true);
            }

            $appointmentEngine->updateDurationForCourseContents($appointment);

            return $appointment;
        });

        return response()->json([
            'appointment' => $this->getAppointmentResponse($appointment),
        ]);
    }

    /**
     * Converts the appointment to a draft
     *
     * @param int $appointmentId
     * @return JsonResponse
     * @throws \Exception
     */
    public function convertToDraft(int $appointmentId): JsonResponse
    {
        $appointment = $this->getAppointment($appointmentId);
        $appointment->is_draft = true;
        $appointment->last_updated_by_id = Auth::user()->id;
        $appointment->save();

        return response()->json([
            'appointment' => $this->getAppointmentResponse($appointment),
        ]);
    }

    /**
     * Cancels the appointment
     *
     * @param int $appointmentId
     * @param Mailer $mailer
     * @return JsonResponse
     * @throws \Exception
     */
    public function cancel(int $appointmentId, Mailer $mailer): JsonResponse
    {
        $appointment = $this->getAppointment($appointmentId);

        if($appointment->is_draft) {
            abort(403);
        }

        $appointment->is_cancelled = true;
        $appointment->last_updated_by_id = Auth::user()->id;
        $appointment->save();

        SendAppointmentUpdateNotifications::dispatch($appointment, AppointmentStartDateWasUpdated::START_DATE_WAS_CANCELLED);

        return response()->json([
            'appointment' => $this->getAppointmentResponse($appointment),
        ]);
    }

    /**
     * Returns the appointment
     *
     * @param int $appointmentId
     * @return Appointment
     * @throws \Exception
     */
    private function getAppointment(int $appointmentId):Appointment
    {
        $appointment = Appointment::tagRights()->findOrFail($appointmentId);

        // Check the access rights
        if ($appointment->app_id != appId()) {
            app()->abort(403);
        }
        return $appointment;
    }

    /**
     * Returns the appointment response
     * @param Appointment $appointment
     * @return array
     */
    private function getAppointmentResponse(Appointment $appointment):array{
        $appointmentEngine = app(AppointmentEngine::class);

        $appointment->load([
            'createdBy',
            'lastUpdatedBy',
            'tags',
        ]);

        $appointment->tags->transform(function($tag) {
            return $tag->id;
        });

        $appointmentEngine->attachParticipantCount(collect([$appointment]), $appointment->app_id);

        $appointment->translations = $appointment->allTranslationRelations->whereIn('language', [defaultAppLanguage(), language()])->values();
        $appointment->unsetRelation('allTranslationRelations');

        return $appointment->toArray();
    }

    /**
     * @param int $appointmentId
     * @return JsonResponse
     */
    public function deleteInformation(int $appointmentId):JsonResponse
    {
        $appointment = $this->getAppointment($appointmentId);
        return response()->json([
            'dependencies' => $appointment->safeRemoveDependees(),
            'blockers' => $appointment->getBlockingDependees(),
        ]);
    }

    /**
     * @param int $appointmentId
     * @param AccessLogEngine $accessLogEngine
     * @return JsonResponse
     */
    public function delete(int $appointmentId, AccessLogEngine $accessLogEngine):JsonResponse
    {
        $appointment = $this->getAppointment($appointmentId);

        $appointment->load([
            'translationRelation',
        ]);

        $result = DB::transaction(function() use ($accessLogEngine, $appointment) {
            return $appointment->safeRemove();
        });

        if($result->isSuccessful()) {
            $accessLogEngine->log(AccessLog::ACTION_DELETE_APPOINTMENT, new AccessLogAppointmentDelete($appointment), Auth::user()->id);

            return response()->json([], 204);
        } else {
            return response()->json($result->getMessages(), 400);
        }
    }

    /**
     * Notifies about the appointment
     *
     * @param int $appointmentId
     * @param Mailer $mailer
     * @return JsonResponse
     * @throws \Exception
     */
    public function notify(int $appointmentId, Mailer $mailer): JsonResponse
    {
        $appointment = $this->getAppointment($appointmentId);

        if(
            $appointment->is_draft ||
            $appointment->is_cancelled ||
            ($appointment->published_at && !$appointment->published_at->isPast())
        ) {
            abort(403);
        }

        SendAppointmentPublishNotifications::dispatch($appointment);

        $appointment->last_notification_sent_at = Carbon::now();
        $appointment->save();

        return response()->json([
            'appointment' => $this->getAppointmentResponse($appointment),
        ]);
    }

    /**
     * Returns all appointments for the active app.
     * @param SimpleAppointmentTransformer $simpleAppointmentTransformer
     * @return JsonResponse
     * @throws \Exception
     */
    public function getAll(SimpleAppointmentTransformer $simpleAppointmentTransformer): JsonResponse
    {
        $appointments = Appointment
            ::where('app_id', appId())
            ->with([
                'translationRelation',
            ])
            ->get();

        return response()->json([
            'appointments' => $simpleAppointmentTransformer->transformAll($appointments),
        ]);
    }

    /**
     * Returns entries for the list call
     *
     * @param Collection $appointments
     * @return Collection
     */
    private function formatAppointments(Collection $appointments):Collection {
        return $appointments->map(function($appointment) {
            return [
                'id' => $appointment->id,
                'name' => $appointment->name,
                'type' => $appointment->type,
                'is_draft' => $appointment->is_draft,
                'cover_image_url' => $appointment->cover_image_url,
                'start_date' => $appointment->start_date,
                'end_date' => $appointment->end_date,
                'published_at' => $appointment->published_at,
                'tags' => $appointment->tags->pluck('id'),
                'participant_count' => $appointment->participant_count,
            ];
        });
    }
}
