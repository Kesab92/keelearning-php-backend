<?php

namespace App\Http\Controllers\Api;

use App\Http\APIError;
use App\Http\Controllers\Controller;
use App\Models\Webinar;
use App\Samba\Samba;
use App\Services\WebinarEngine;
use Illuminate\Http\JsonResponse;
use Response;

class WebinarsController extends Controller
{
    /**
     * Returns a list of all webinars.
     *
     * @param WebinarEngine $webinarEngine
     * @return JsonResponse
     */
    public function webinars(WebinarEngine $webinarEngine)
    {
        $webinars = $webinarEngine->getUsersWebinars(user());

        $webinarEngine->attachUserCounts($webinars);

        $webinars->transform(function ($webinar) {
            return [
                'id' => $webinar->id,
                'topic' => $webinar->topic,
                'description' => $webinar->description,
                'starts_at' => $webinar->starts_at->format('Y-m-d H:i:s'),
                'duration_minutes' => $webinar->duration_minutes,
                'show_recordings' => $webinar->show_recordings,
                'user_count' => $webinar->user_count,
            ];
        });

        return Response::json([
            'webinars' => $webinars,
        ]);
    }

    public function recordings(WebinarEngine $webinarEngine)
    {
        $user = user();
        $webinars = $webinarEngine->getUsersRecordingVisibleWebinars($user);
        $sambaIds = $webinars->pluck('samba_id');

        $api = Samba::forCustomer($user->app->samba_id);

        $recordings = $api
            ->withAppSpecificAuth($user->app->samba_token)
            ->getRecordings($sambaIds);

        // Transform recordings to output data format
        $recordings = $recordings->map(function ($recording) use ($webinars) {
            /** @var Webinar $webinar */
            $webinar = $webinars->where('samba_id', $recording['session_id'])->first();
            if (! $webinar) {
                return null;
            }

            return [
                'topic' => $webinar->topic,
                'download_link' => $recording['download_link'],
                'created_at' => $recording['creation_date'],
                'duration_minutes' => $recording['duration'] / 1000 / 60, // Duration is stored in ms
                'webinar_id' => $webinar->id,
                'webinar_duration_minutes' => $webinar->duration_minutes,
            ];
        })
            ->sortByDesc('created_at')
            ->filter()
            ->values();

        return Response::json([
            'recordings' => $recordings,
        ]);
    }

    /**
     * Joins a webinar.
     *
     * @param $webinarId
     * @param WebinarEngine $webinarEngine
     * @return JsonResponse
     */
    public function join($webinarId, WebinarEngine $webinarEngine)
    {
        $webinar = Webinar::findOrFail($webinarId);
        $user = user();

        if (! $webinarEngine->canJoin($user, $webinar)) {
            return new APIError('You are not allowed to join this webinar', 403);
        }

        try {
            $joinLink = $webinarEngine->getJoinLink($webinar, $user);

            return Response::json([
                'join_link' => $joinLink,
            ]);
        } catch (\Exception $e) {
            report($e);

            return new APIError('Could not join this webinar. Please try again.', 500);
        }
    }
}
