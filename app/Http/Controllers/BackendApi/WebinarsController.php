<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Mail\Mailer;
use App\Models\Tag;
use App\Models\User;
use App\Models\Webinar;
use App\Models\WebinarAdditionalUser;
use App\Samba\Samba;
use App\Services\WebinarEngine;
use App\Traits\PersonalData;
use Auth;
use Carbon\Carbon;
use DB;
use Response;

class WebinarsController extends Controller
{
    use PersonalData;
    private $webinarEngine;

    public function __construct(WebinarEngine $webinarEngine)
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:webinars,webinars-personaldata');
        $this->webinarEngine = $webinarEngine;
        $this->personalDataRightsMiddleware('webinars');
    }

    public function getWebinars()
    {
        $webinars = Webinar::where('app_id', appId())
            ->with('tags')
            ->withCount('additionalUsers')
            ->orderBy('id', 'DESC')
            ->get()
            ->map(function ($webinar) {
                return $this->formatWebinarListEntry($webinar);
            });
        $tags = Tag::ofApp(appId())
            ->with('tagGroup')
            ->orderBy('label')
            ->get();

        return Response::json([
            'success'   => true,
            'tags'      => $tags,
            'webinars'  => $webinars,
        ]);
    }

    public function getWebinar($id)
    {
        $webinar = Webinar::where('app_id', appId())
            ->with('additionalUsers.user')
            ->findOrFail($id);

        return Response::json([
            'success' => true,
            'webinar' => $this->formatWebinar($webinar),
        ]);
    }

    public function createWebinar()
    {
        if (! request()->input('topic')) {
            return Response::json([
                'error' => 'Thema darf nicht leer sein!',
                'success' => false,
            ]);
        }

        $webinar = new Webinar();
        $webinar->app_id = appId();
        $this->updateWebinarDataFromRequest($webinar);

        return Response::json([
            'success' => true,
            'webinar' => $this->formatWebinar($webinar),
        ]);
    }

    public function updateWebinar($id)
    {
        if (! request()->input('topic')) {
            return Response::json([
                'error' => 'Thema darf nicht leer sein!',
                'success' => false,
            ]);
        }

        $webinar = Webinar::where('app_id', appId())
            ->findOrFail($id);

        $resetReminder =
            request()->input('send_reminder')
            && $webinar->reminder_sent_at
            && $webinar->starts_at != request()->input('starts_at')
            && Carbon::parse(request()->input('starts_at'))->isFuture();
        if ($resetReminder) {
            $webinar->reminder_sent_at = null;
        }
        $this->updateWebinarDataFromRequest($webinar);

        return Response::json([
            'success'        => true,
            'reset_reminder' => $resetReminder,
            'webinar'        => $this->formatWebinarListEntry($webinar),
        ]);
    }

    public function deleteWebinar($id)
    {
        $webinar = Webinar::where('app_id', appId())->findOrFail($id);

        $samba = Samba::forCustomer($webinar->app->samba_id);
        $samba->withAppSpecificAuth($webinar->app->samba_token);
        $samba->deleteSession($webinar->samba_id);

        $recordings = $samba->getRecordings(collect([$webinar->samba_id]));
        foreach ($recordings as $recording) {
            $samba->deleteRecording($recording['id']);
        }

        $result = $webinar->safeRemove();
        if (! $result->success) {
            return Response::json([
                'success' => false,
                'error'   => 'Webinar konnte nicht gelöscht werden, bitte kontaktieren Sie den Support.',
            ]);
        }

        return Response::json([
            'success' => true,
        ]);
    }

    public function getRecordings($id)
    {
        /** @var Webinar $webinar */
        $webinar = Webinar::where('app_id', appId())
            ->findOrFail($id);

        $samba = Samba::forCustomer($webinar->app->samba_id);
        $samba->withAppSpecificAuth($webinar->app->samba_token);
        $recordings = $samba->getRecordings(collect([$webinar->samba_id]));

        return Response::json([
            'success'    => true,
            'recordings' => $recordings->values(),
        ]);
    }

    public function deleteRecording($id)
    {
        /** @var Webinar $webinar */
        $webinar = Webinar::where('app_id', appId())
            ->findOrFail($id);

        $samba = Samba::forCustomer($webinar->app->samba_id);
        $samba->withAppSpecificAuth($webinar->app->samba_token);
        $recording = $samba->getRecording(request()->post('recording_id'));
        if (! $recording || $recording['session_id'] != $webinar->samba_id) {
            app()->abort(403);
        }
        $samba->deleteRecording($recording['id']);

        return Response::json([
            'success' => true,
        ]);
    }

    public function getJoinLink($additional_user_id)
    {
        $additionalUser = WebinarAdditionalUser::findOrFail($additional_user_id);
        if ($additionalUser->webinar->app_id != appId()) {
            app()->abort(403);
        }

        return Response::json([
            'success'   => true,
            'join_link' => $this->webinarEngine->getAdditionalUserJoinLink($additionalUser),
        ]);
    }

    public function sendSingleInvitation()
    {
        $additionalUser = WebinarAdditionalUser::findOrFail(request()->input('additional_user_id'));
        if ($additionalUser->webinar->app_id != appId()) {
            app()->abort(403);
        }
        $mailer = app(Mailer::class);
        if ($additionalUser->user_id) {
            $mailer->sendWebinarReminder($additionalUser->user, $additionalUser->webinar);
        } else {
            $mailer->sendWebinarReminderExternal($additionalUser);
        }

        return Response::json([
            'success' => true,
        ]);
    }

    private function formatWebinar(Webinar $webinar)
    {
        $webinarData = $webinar->toArray();
        if ($this->webinarEngine->canJoin(Auth::user(), $webinar)) {
            $webinarData['join_link'] = $webinar->app->getDefaultAppProfile()->app_hosted_at.'/webinar/'.$webinar->id;
        }
        $webinarData['tag_ids'] = $webinar->tags()->pluck('tags.id');
        $webinarData['additional_users'] = $webinar->additionalUsers->map(function ($additionalUser) {
            /** @var WebinarAdditionalUser $additionalUser */
            $data = [
                'id' => $additionalUser->id,
                'user_id' => $additionalUser->user_id,
                'role' => $additionalUser->role,
            ];
            if ($additionalUser->user) {
                $data['external'] = false;
                if ($this->showPersonalData) {
                    $data['name'] = $additionalUser->user->username;
                } else {
                    $data['name'] = 'App-User';
                }
                if ($this->showEmails) {
                    $data['email'] = $additionalUser->user->getMailBackend();
                }
            } else {
                $data['external'] = true;
                $data['email'] = $additionalUser->email;
                $data['name'] = $additionalUser->name;
            }

            return $data;
        });

        return $webinarData;
    }

    private function formatWebinarListEntry(Webinar $webinar)
    {
        return [
            'id'                     => $webinar->id,
            'additional_users_count' => $webinar->additionalUsers()->count(),
            'duration_minutes'       => $webinar->duration_minutes,
            'join_link'              => $webinar->app->getDefaultAppProfile()->app_hosted_at.'/webinar/'.$webinar->id,
            'starts_at'              => $webinar->starts_at->format('Y-m-d H:i:s'),
            'tag_ids'                => $webinar->tags()->pluck('tags.id'),
            'topic'                  => $webinar->topic,
        ];
    }

    private function updateWebinarDataFromRequest(Webinar $webinar)
    {
        DB::transaction(function () use ($webinar) {
            $webinar->topic = request()->input('topic');
            $webinar->description = request()->input('description');
            $webinar->starts_at = request()->input('starts_at');
            $webinar->duration_minutes = request()->input('duration_minutes') ?: null;
            $webinar->send_reminder = (bool) request()->input('send_reminder');
            $webinar->show_recordings = (bool) request()->input('show_recordings');
            $webinar->save();

            $webinar->tags()->sync(request()->input('tag_ids'));

            if (request()->has('additional_users')) {
                $this->syncAdditionalUsers($webinar);
            }

            $this->webinarEngine->syncToSamba($webinar);
        });
    }

    /**
     * Syncs the additional users relation from the request with our database.
     *
     * @param Webinar $webinar
     */
    private function syncAdditionalUsers(Webinar $webinar)
    {
        $sendLateReminder = $webinar->send_reminder && $webinar->reminder_sent_at;
        $mailer = app(Mailer::class);
        $existingAdditionalUsers = $webinar->additionalUsers;
        $newAdditionalUsers = collect(request()->input('additional_users'));
        $newAdditionalUsers->each(function ($newAdditionalUser) use ($existingAdditionalUsers, $mailer, $sendLateReminder, $webinar) {
            $existingAdditionalUser = null;
            if ($newAdditionalUser['id'] !== null) {
                // Update existing additional users
                $existingAdditionalUser = $existingAdditionalUsers->where('id', $newAdditionalUser['id'])
                    ->first();
                if (! $existingAdditionalUser) {
                    return;
                }
            }
            if (isset($newAdditionalUser['user_id'])) {
                $existingAdditionalUser = $existingAdditionalUsers->where('user_id', $newAdditionalUser['user_id'])
                    ->first();
            }
            $relevantAdditionalUser = null;

            if ($existingAdditionalUser) {
                // Update the existing additional user
                $existingAdditionalUser->role = $newAdditionalUser['role'];
                if ($existingAdditionalUser->user_id === null) {
                    $existingAdditionalUser->name = $newAdditionalUser['name'];
                    $existingAdditionalUser->email = $newAdditionalUser['email'];
                }
                $relevantAdditionalUser = $existingAdditionalUser;
            } else {
                // Create new additional users
                $additionalUser = new WebinarAdditionalUser();
                $additionalUser->webinar_id = $webinar->id;
                $additionalUser->role = $newAdditionalUser['role'];

                if ($newAdditionalUser['user_id'] !== null) {
                    // Create new internal users
                    $user = User::where('app_id', appId())->where('id', $newAdditionalUser['user_id'])->first();
                    if (! $user) {
                        return;
                    }
                    $additionalUser->user_id = $user->id;
                } else {
                    // Create new external users
                    $additionalUser->name = $newAdditionalUser['name'];
                    $additionalUser->email = $newAdditionalUser['email'];
                }
                $relevantAdditionalUser = $additionalUser;
            }

            // Sync / update participant
            if ($relevantAdditionalUser->isDirty()) {
                // We only want to sync anything if anything changed
                $relevantAdditionalUser->save();
                // See if we already have a connected participant for this additional user
                $participant = $relevantAdditionalUser->participant;
                if (! $participant && $relevantAdditionalUser->user_id) {
                    // We don't have a participant, but there might be one...
                    $participant = $webinar->participants->where('user_id', $relevantAdditionalUser->user_id)->first();
                    if ($participant) {
                        // We found a not yet connected participant. Connect it.
                        $participant->webinar_additional_user_id = $relevantAdditionalUser->id;
                        $participant->save();
                        $relevantAdditionalUser->refresh();
                    }
                }
                if ($participant) {
                    // We have a participant, update the join link
                    $participant->join_link = $this->webinarEngine
                        ->updateSessionInvitee($relevantAdditionalUser);
                    $participant->save();
                }
                // everyone else got their invitation already?
                // invite new/updated user (join url changes) too
                if ($sendLateReminder) {
                    if ($relevantAdditionalUser->user_id) {
                        // internal users' link does not change, so only send for new users
                        if (! $existingAdditionalUser) {
                            $mailer->sendWebinarReminder($relevantAdditionalUser->user, $webinar);
                        }
                    } else {
                        $mailer->sendWebinarReminderExternal($relevantAdditionalUser);
                    }
                }
            }
        });

        // Delete deleted additional users
        $newAdditionalUserIds = $newAdditionalUsers->pluck('id');
        foreach ($existingAdditionalUsers->whereNotIn('id', $newAdditionalUserIds) as $additionalUser) {
            if ($additionalUser->participant) {
                $this->webinarEngine->leaveSession($additionalUser->participant);
                $additionalUser->participant->delete();
            }
            $additionalUser->delete();
        }
    }
}
