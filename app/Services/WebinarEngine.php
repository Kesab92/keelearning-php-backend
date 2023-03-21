<?php

namespace App\Services;

use App\Models\User;
use App\Models\Webinar;
use App\Models\WebinarAdditionalUser;
use App\Models\WebinarParticipant;
use App\Samba\Data\CreateSession;
use App\Samba\Data\JoinSession;
use App\Samba\Data\LeaveSession;
use App\Samba\Data\UpdateSession;
use App\Samba\Data\UpdateSessionInvitee;
use App\Samba\Samba;
use App\Samba\SambaConnectionException;
use DB;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class WebinarEngine
{
    // Amount of minutes a user can join after a webinar has finished
    const WEBINAR_GRACE_JOIN_PERIOD = 60;

    /**
     * Returns all webinars the user has access to and can still join (they have not yet expired).
     *
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Collection|Webinar[]|\Illuminate\Support\Collection
     */
    public function getUsersWebinars(User $user)
    {
        return $this->getBaseUserWebinarsQuery($user)
            ->where(function ($query) {
                $query->where(DB::raw('DATE_ADD(starts_at, INTERVAL duration_minutes + '.self::WEBINAR_GRACE_JOIN_PERIOD.' MINUTE)'), '>=', \Carbon\Carbon::now())
                    ->orWhereNull('duration_minutes');
            })
            ->get();
    }

    /**
     * Returns all webinars the user has access to (including expired ones).
     *
     * @param User $user
     * @return mixed
     */
    public function getAllUsersWebinars(User $user)
    {
        return $this->getBaseUserWebinarsQuery($user)->get();
    }

    /**
     * Returns all registered users with access to the given webinar.
     *
     * @param Webinar $webinar
     * @return mixed
     */
    public function getWebinarUsers(Webinar $webinar)
    {
        $additionalUsers = $webinar->additionalUsers()
            ->with('user')
            ->whereNotNull('user_id')
            ->get()
            ->pluck('user');
        if (! $webinar->tags()->count()) {
            $webinarUsers = User::whereNotIn('id', $additionalUsers->pluck('id'))
                ->where('app_id', $webinar->app_id)
                ->get();
        } else {
            $webinarUsers = User::whereNotIn('users.id', $additionalUsers->pluck('id'))
                ->where('app_id', $webinar->app_id)
                ->whereHas('tags', function ($query) use ($webinar) {
                    $query->whereIn('tags.id', $webinar->tags()->pluck('tags.id'));
                })
                ->get();
        }

        return $additionalUsers->concat($webinarUsers);
    }

    /**
     * Returns a query fetching all registered users with access to the given webinar,
     * except for additional Users.
     *
     * @param Webinar $webinar
     * @return mixed
     */
    public function getWebinarTagUsersQuery(Webinar $webinar)
    {
        $additionalUserIds = $webinar->additionalUsers()->whereNotNull('user_id')->pluck('user_id');
        if (! $webinar->tags()->count()) {
            return User::whereNotIn('id', $additionalUserIds)
                ->where('app_id', $webinar->app_id);
        }

        return User::whereNotIn('users.id', $additionalUserIds)
            ->where('app_id', $webinar->app_id)
            ->whereHas('tags', function ($query) use ($webinar) {
                $query->whereIn('tags.id', $webinar->tags()->pluck('tags.id'));
            });
    }

    /**
     * Returns all webinars the user has access to and they can see recordings of.
     *
     * @param User $user
     * @return mixed
     */
    public function getUsersRecordingVisibleWebinars(User $user)
    {
        return $this->getBaseUserWebinarsQuery($user)
            ->where('show_recordings', true)
            ->get();
    }

    /**
     * Returns a query to get a users webinars.
     *
     * @param User $user
     * @return Builder
     */
    private function getBaseUserWebinarsQuery(User $user)
    {
        $userTags = $user->tags()->pluck('tags.id');

        return Webinar::where('app_id', $user->app_id)
            ->where(function ($query) use ($userTags, $user) {
                $query->doesntHave('tags')
                    ->orWhereHas('tags', function ($query) use ($userTags) {
                        $query->whereIn('tags.id', $userTags);
                    })
                    ->orWhereHas('additionalUsers', function ($query) use ($user) {
                        $query->where('webinar_additional_users.user_id', $user->id);
                    });
            });
    }

    /**
     * Checks if the given user could join the given webinar.
     *
     * @param User $user
     * @param Webinar $webinar
     * @return bool
     */
    public function canJoin(User $user, Webinar $webinar)
    {
        if ($webinar->app_id !== $user->app_id) {
            return false;
        }

        if ($webinar->isExpired()) {
            return false;
        }

        // Check if the user has access via the additionalUsers relation
        if ($webinar->additionalUsers()->where('user_id', $user->id)->count() > 0) {
            return true;
        }

        // Check if the user has access via tags
        $webinarTags = $webinar->tags()->pluck('tags.id');
        if (! $webinarTags->count()) {
            return true;
        }

        $userTags = $user->tags()->pluck('tags.id');
        if ($userTags->intersect($webinarTags)->count() > 0) {
            return true;
        }

        return false;
    }

    /**
     * Retrieves the session join link
     * either for the given user,
     * or for a user identified by $additionalUser.
     *
     * @param Webinar $webinar
     * @param User|null $user
     * @param WebinarAdditionalUser|null $additionalUser
     * @return string
     * @throws SambaConnectionException
     */
    public function getJoinLink(Webinar $webinar, User $user = null, WebinarAdditionalUser $additionalUser = null)
    {
        $participant = null;
        if ($user) {
            $participant = $webinar->participants()->where('user_id', $user->id)->first();
        }
        if ($additionalUser) {
            $participant = $webinar->participants()->where('webinar_additional_user_id', $additionalUser->id)->first();
        }

        if (! $participant) {
            $api = Samba::forCustomer($webinar->app->samba_id);

            if ($user && ! $additionalUser) {
                $additionalUser = $webinar->additionalUsers()->where('user_id', $user->id)->first();
            }

            $joinSession = new JoinSession();
            $joinSession->setSessionId($webinar->samba_id);
            if ($user) {
                $joinSession->setEmail($user->email);
                $joinSession->setFirstName($user->username);
            } elseif ($additionalUser) {
                $joinSession->setEmail($additionalUser->email);
                $joinSession->setFirstName($additionalUser->name);
            }

            if ($additionalUser) {
                $joinSession->setRole($additionalUser->role);
            }
            $response = $api->joinSession($joinSession);

            $participant = new WebinarParticipant();
            $participant->webinar_id = $webinar->id;
            $participant->join_link = $response['personal_session_link'];
            $participant->samba_invitee_id = $response['id'];
            if ($user) {
                $participant->user_id = $user->id;
            }
            if ($additionalUser) {
                $participant->webinar_additional_user_id = $additionalUser->id;
            }
            $participant->save();
        }

        return $participant->join_link;
    }

    public function getAdditionalUserJoinLink(WebinarAdditionalUser $additionalUser)
    {
        $appSettings = new AppSettings($additionalUser->webinar->app->id);
        if ($additionalUser->user_id) {
            if($appSettings->getValue('has_candy_frontend')) {
                return $additionalUser->webinar->app->getDefaultAppProfile()->app_hosted_at.'/webinars/'.$additionalUser->webinar->id;
            } else {
                return $additionalUser->webinar->app->getDefaultAppProfile()->app_hosted_at.'/webinar/'.$additionalUser->webinar->id;
            }
        }

        $joinLink = explode('/', $this->getJoinLink($additionalUser->webinar, null, $additionalUser));
        $token = end($joinLink);

        if($appSettings->getValue('has_candy_frontend')) {
            return $additionalUser->webinar->app->getDefaultAppProfile()->app_hosted_at.'/public/webinars/room/'.$token;
        } else {
            return $additionalUser->webinar->app->getDefaultAppProfile()->app_hosted_at.'/public/webinar/'.$token;
        }
    }

    public function updateSessionInvitee(WebinarAdditionalUser $additionalUser)
    {
        $api = Samba::forCustomer($additionalUser->participant->webinar->app->samba_id);
        $updateSessionInvitee = new UpdateSessionInvitee();
        $updateSessionInvitee->setSessionId($additionalUser->participant->webinar->samba_id);
        $updateSessionInvitee->setInviteeId($additionalUser->participant->samba_invitee_id);
        $updateSessionInvitee->setRole($additionalUser->role);
        $user = null;
        if($additionalUser->user_id) {
            $user = User
                ::where('app_id', $additionalUser->participant->webinar->app->id)
                ->where('id', $additionalUser->user_id)
                ->first();
        }
        if ($user) {
            $updateSessionInvitee->setEmail($user->email);
            $updateSessionInvitee->setFirstName($user->username);
        } elseif ($additionalUser) {
            $updateSessionInvitee->setEmail($additionalUser->email);
            $updateSessionInvitee->setFirstName($additionalUser->name);
        }
        $response = $api->updateSessionInvitee($updateSessionInvitee);

        return $response['personal_session_link'];
    }

    public function leaveSession(WebinarParticipant $participant)
    {
        $api = Samba::forCustomer($participant->webinar->app->samba_id);
        $leaveSession = new LeaveSession();
        $leaveSession->setSessionId($participant->webinar->samba_id);
        $leaveSession->setInviteeId($participant->samba_invitee_id);
        $api->leaveSession($leaveSession);
    }

    /**
     * Syncs a session to samba.
     *
     * @param Webinar $webinar
     * @throws SambaConnectionException
     */
    public function syncToSamba(Webinar $webinar)
    {
        $api = Samba::forCustomer($webinar->app->samba_id);
        if (! $webinar->samba_id) {
            $createSession = new CreateSession();
            $createSession->setTopic($webinar->topic);
            $createSession->setDescription($webinar->description);
            $createSession->setDuration($webinar->duration_minutes);
            $createSession->setStartTime($webinar->starts_at);
            $createSession->setKeeunitId($webinar->id);
            $createSession->setSambaCustomerId($webinar->app->samba_id);
            $createdSession = $api->createSession($createSession);
            $webinar->samba_id = $createdSession['id'];
            $webinar->save();
        } else {
            $updateSession = new UpdateSession();
            $updateSession->setSambaId($webinar->samba_id);
            $updateSession->setTopic($webinar->topic);
            $updateSession->setDescription($webinar->description);
            $updateSession->setDuration($webinar->duration_minutes);
            $updateSession->setStartTime($webinar->starts_at);
            $updateSession->setSambaCustomerId($webinar->app->samba_id);
            $api->updateSession($updateSession);
        }
    }

    public function attachUserCounts(Collection $webinars)
    {
        if (! $webinars->count()) {
            return $webinars;
        }
        $appId = $webinars->first()->app_id;
        $webinars->load('tags');
        $webinars->loadCount('additionalExternalUsers');
        $appUserCount = User::active()->where('app_id', $appId)->count();

        $webinars->transform(function (Webinar $webinar) use ($appUserCount) {
            $userCount = $webinar->additional_external_users_count;
            if (! $webinar->tags->count()) {
                $userCount += $appUserCount;
            } else {
                $userIdQuery = WebinarAdditionalUser
                    ::where('webinar_id', $webinar->id)
                    ->whereNotNull('user_id')
                    ->select('user_id')
                    ->union(
                        DB::table('tag_user')
                        ->whereIn('tag_id', $webinar->tags->pluck('id'))
                        ->select('user_id')
                        ->groupBy('user_id')
                    );
                $userCount += User
                    ::active()
                    ->where('app_id', $webinar->app_id)
                    ->joinSub($userIdQuery, 'ids', 'ids.user_id', '=', 'users.id')
                    ->count();
            }
            $webinar->user_count = $userCount;

            return $webinar;
        });

        return $webinars;
    }
}
