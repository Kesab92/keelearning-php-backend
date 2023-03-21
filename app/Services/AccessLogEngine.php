<?php

namespace App\Services;

use App\Models\AccessLog;
use App\Models\User;
use App\Services\AccessLogMeta\AccessLogMeta;
use Auth;
use Request;

class AccessLogEngine
{
    /**
     * Logs a user action.
     * @param $action
     * @param AccessLogMeta $meta
     * @param null $creatorId
     */
    public function log($action, AccessLogMeta $meta = null, $creatorId = null)
    {
        $accessLog = new AccessLog();
        $accessLog->action = $action;
        if ($creatorId === null) {
            $accessLog->user_id = Auth::user()->id;
        } else {
            $accessLog->user_id = $creatorId;
        }
        $ip = ip2long(Request::ip());
        if ($ip !== false && $ip !== -1) {
            $accessLog->ip = $ip;
        } else {
            $accessLog->ip = null;
        }
        if ($meta) {
            $accessLog->meta = $meta->getMeta();
        }
        $accessLog->save();
    }

    /**
     * Returns all access logs for the given app.
     *
     * @param       $appId
     *
     * @param array $specificUsers
     *
     * @return AccessLog[]|\Illuminate\Support\Collection
     */
    public function get($appId, $specificUsers = [])
    {
        $q = AccessLog::with('user')
            ->select('access_logs.*')
            ->join('users', 'users.id', '=', 'access_logs.user_id');

        if ($specificUsers) {
            $q->whereIn('users.id', $specificUsers);
        }

        $q
            ->where(function ($query) use ($appId) {
                $query->where('users.app_id', $appId)
                      ->orWhereIn('users.id', User::getSuperAdminIds());
            })
            ->orderBy('access_logs.created_at', 'desc')
            ->take(1000);

        return $q->get();
    }
}
