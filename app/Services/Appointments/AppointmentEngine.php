<?php

namespace App\Services\Appointments;

use App\Models\Appointments\Appointment;
use App\Models\Courses\CourseContent;
use App\Models\User;
use App\Services\ConfigurationLogicInvestigator;
use App\Services\ICalendar\ContentTypes\EventContent;
use App\Services\ICalendar\IEvent;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AppointmentEngine
{
    /**
     * Gets all appointments for a given app user.
     *
     * @param User $user
     * @return Collection
     */
    public function getUserAppointments(User $user): Collection
    {
        return Appointment::ofApp($user->app_id)
            ->visible()
            ->leftJoin('appointment_tags', 'appointments.id', '=', 'appointment_tags.appointment_id')
            ->where(function ($query) use ($user) {
                $query->whereNull('appointment_tags.tag_id')
                    ->orWhereIn('appointment_tags.tag_id', $user->tags()->pluck('tags.id'));
            })
            ->select('appointments.*')
            ->with('translationRelation')
            ->get();
    }

    /**
     * Creates a query for appointments using filter
     * @param int $appId
     * @param $admin
     * @param null $search
     * @param null $tags
     * @param null $filter
     * @param null $orderBy
     * @param false $descending
     * @return Appointment|\Illuminate\Database\Eloquent\Builder
     */
    public function appointmentsFilterQuery(int $appId, $admin, $search = null, $tags = null, $filter = null, $orderBy = null, bool $descending = false)
    {
        $appointmentsQuery = Appointment::where('app_id', $appId);

        if ($admin) {
            $appointmentsQuery = $appointmentsQuery->tagRights($admin);
        }

        if ($search) {
            $appointmentStartDateFrom = null;
            $appointmentStartDateTo = null;

            if (preg_match('@^20\d\d$@', $search, $result) != false) {
                $appointmentStartDateFrom = Carbon::createFromFormat('Y-m-d',  $search . '-01-01')->startOfDay();
                $appointmentStartDateTo = Carbon::createFromFormat('Y-m-d',  $search . '-12-31')->endOfDay();
            }
            if (preg_match('@^20\d\d-[0-1]\d$@', $search, $result) != false) {
                $appointmentStartDateFrom = Carbon::createFromFormat('Y-m-d',  $search . '-01')->startOfDay();
                $appointmentStartDateTo = Carbon::createFromFormat('Y-m-d',  $search . '-01')->lastOfMonth()->endOfDay();
            }
            if (preg_match('@^[0-3]\d.[0-1]\d\.?$@', $search, $result) != false && $this->isCorrectCurrentYearDate(urtrim($search, '.'))) {
                $appointmentStartDateFrom = Carbon::createFromFormat('d.m.Y',  urtrim($search, '.') . '.' . Carbon::now()->year)->startOfDay();
                $appointmentStartDateTo = Carbon::createFromFormat('d.m.Y',  urtrim($search, '.') . '.' . Carbon::now()->year)->endOfDay();
            }
            if (preg_match('@^[0-3]\d.[0-1]\d\.20\d\d$@', $search, $result) != false && $this->isCorrectFullDate($search)) {
                $appointmentStartDateFrom = Carbon::createFromFormat('d.m.Y',  $search)->startOfDay();
                $appointmentStartDateTo = Carbon::createFromFormat('d.m.Y',  $search)->endOfDay();
            }

            $appointmentsQuery->where(function ($query) use ($search, $appId, $appointmentStartDateTo, $appointmentStartDateFrom) {
                if($appointmentStartDateFrom && $appointmentStartDateTo) {
                    $query
                        ->where('start_date', '>=', $appointmentStartDateFrom)
                        ->where('start_date', '<=', $appointmentStartDateTo);
                }

                $matchingTitles = DB::table('appointment_translations')
                    ->join('appointments', 'appointment_translations.appointment_id', '=', 'appointments.id')
                    ->select('appointments.id')
                    ->where('appointments.app_id', $appId)
                    ->whereRaw('appointment_translations.name LIKE ?', '%'.escapeLikeInput($search).'%');
                $query->orWhere(function ($query) use ($search, $matchingTitles) {
                    $query->whereIn('appointments.id', $matchingTitles)
                        ->orWhere('appointments.id', extractHashtagNumber($search));
                });
            });
        }
        if ($filter === 'active') {
            $appointmentsQuery->where('end_date', '>=', Carbon::now());
        }
        if ($filter === 'expired') {
            $appointmentsQuery->where('end_date', '<', Carbon::now());
        }
        if ($filter === 'without_participants') {
            $appointmentsQuery
                ->select('appointments.*')
                ->leftJoin('appointment_tags', 'appointments.id', '=', 'appointment_tags.appointment_id')
                ->leftJoin('tag_user', 'appointment_tags.tag_id', '=', 'tag_user.tag_id')
                ->whereNotNull('appointment_tags.tag_id')
                ->groupBy('appointments.id')
                ->havingRaw("MAX(tag_user.user_id) IS NULL");
        }
        if ($filter === 'active_without_participants') {
            $appointmentsQuery
                ->whereIn('id', (new ConfigurationLogicInvestigator)->activeAppointmentsWithoutParticipants($appId));
        }
        if ($tags && count($tags)) {
            $appointmentsWithoutTag = in_array(-1, $tags);
            $tags = array_filter($tags, function ($tag) {
                return $tag !== '-1';
            });
            if (count($tags)) {
                $appointmentsQuery->where(function (\Illuminate\Database\Eloquent\Builder $query) use ($tags, $appointmentsWithoutTag) {
                    $query->whereHas('tags', function ($query) use ($tags) {
                        $query->whereIn('tags.id', $tags);
                    });
                    if ($appointmentsWithoutTag) {
                        $query->orWhereDoesntHave('tags');
                    }
                });
            } else {
                $appointmentsQuery->doesntHave('tags');
            }
        }

        if ($orderBy) {
            switch ($orderBy) {
                case 'name':
                    $appointmentsQuery = Appointment::orderByTranslatedField($appointmentsQuery, 'name', $appId, $descending ? 'desc' : 'asc');
                    break;
                default:
                    $appointmentsQuery->orderBy('appointments.'.$orderBy, $descending ? 'desc' : 'asc');
                    break;
            }
        }

        return $appointmentsQuery;
    }

    /**
     * Attaches participant counts for the appointments
     *
     * @param Collection $appointments
     * @param int $appId
     * @return void
     */
    public function attachParticipantCount(Collection $appointments, int $appId) {
        $appointmentIds = $appointments->pluck('id');

        $queryAppointmentsWithoutTags = DB::table('appointments')
            ->select('appointments.id')
            ->selectSub($this->activeUsersQuery($appId)->selectRaw('COUNT(*) as count'), 'count')
            ->leftJoin('appointment_tags', 'appointment_tags.appointment_id', 'appointments.id')
            ->whereIn('appointments.id', $appointmentIds)
            ->whereNull('appointment_tags.tag_id')
            ->where('appointments.app_id', $appId);

        $participantCountPerAppointment = $this->appointmentUsersQuery($appId)
            ->select('appointments.id')
            ->selectRaw('COUNT(DISTINCT users.id) as count')
            ->whereNotNull('appointment_tags.tag_id')
            ->whereIn('appointments.id', $appointmentIds)
            ->groupBy('appointments.id')
            ->union($queryAppointmentsWithoutTags)
            ->pluck('count', 'id');

        $appointments->transform(function ($appointment) use ($participantCountPerAppointment) {
            if($participantCountPerAppointment->has($appointment->id)) {
                $appointment->participant_count = $participantCountPerAppointment->get($appointment->id);
            } else {
                $appointment->participant_count = null;
            }
            return $appointment;
        });
    }

    public function updateDurationForCourseContents(Appointment $appointment, array $courseContentIds = []) {
        $duration = $appointment->end_date->diffInMinutes($appointment->start_date);

        $courseContents = CourseContent
            ::where('type', CourseContent::TYPE_APPOINTMENT)
            ->where('foreign_id', $appointment->id);

        if(!empty($courseContentIds)) {
            $courseContents->whereIn('id', $courseContentIds);
        }

        $courseContents->update([
                'duration' => $duration,
            ]);
    }

    /**
     * Returns the appointment in the ICS event format
     * @param Appointment $appointment
     * @return string
     */
    public function getIcsEventContent(Appointment $appointment) {
        $updatedAt = $appointment->updated_at;

        if($appointment->published_at && $appointment->published_at->gt($updatedAt)) {
            $updatedAt = $appointment->published_at;
        }

        $eventContent = app(EventContent::class, [
            'startDate' => $appointment->start_date,
            'endDate' => $appointment->end_date,
            'createdAt' => $appointment->published_at ?: $appointment->created_at,
            'updatedAt' => $updatedAt,
            'status' => 'CONFIRMED',
            'description' => preg_replace("/\n+/", "\n", $appointment->description),
            'summary' => $appointment->name,
            'url' => Str::startsWith($appointment->location, 'http') ? $appointment->location : null,
            'location' => $appointment->location,
        ]);

        $iEvent = app(IEvent::class, ['content' => $eventContent]);
        return $iEvent->getContent();
    }

    /**
     * Returns the query with active users
     *
     * @param int $appId
     * @return Builder
     */
    private function activeUsersQuery(int $appId): Builder
    {
        return $this->activeUsersQueryRestriction(DB::table('users'), $appId);
    }

    /**
     * Adds restrictions for user getting
     *
     * @param Builder $query
     * @param int $appId
     * @return Builder
     */
    private function activeUsersQueryRestriction(Builder $query, int $appId): Builder
    {
        $query->whereNull('users.deleted_at')
            ->where('users.is_bot', 0)
            ->where('users.is_dummy', 0)
            ->where('users.is_api_user', 0)
            ->where('users.active', 1)
            ->where('users.app_id', $appId);
        return $query;
    }

    /**
     * Returns the query with appointments' users
     * @param int $appId
     * @return Builder
     */
    private function appointmentUsersQuery(int $appId):Builder
    {
        return DB::table('appointments')
            ->leftJoin('appointment_tags', 'appointment_tags.appointment_id', 'appointments.id')
            ->leftJoin('tag_user', 'tag_user.tag_id', 'appointment_tags.tag_id')
            ->leftJoin('users', function ($join) use ($appId) {
                $join->on('users.id', 'tag_user.user_id');
                $this->activeUsersQueryRestriction($join, $appId);
            })
            ->where('appointments.app_id', $appId);
    }

    /**
     * Checks if the date of the current year is valid
     *
     * @param string $dayAndMonth
     * @return bool
     */
    private function isCorrectCurrentYearDate (string $dayAndMonth): bool
    {
        $dateAsString = $dayAndMonth . '.' . Carbon::now()->year;
        return $this->isCorrectFullDate($dateAsString);
    }

    /**
     * Checks if the date is valid
     *
     * @param string $dateAsString
     * @return bool
     */
    private function isCorrectFullDate (string $dateAsString): bool
    {
        return Carbon::createFromFormat('d.m.Y', $dateAsString)->format('d.m.Y') === $dateAsString;
    }
}
