<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Services\AccessLogEngine;
use Illuminate\Http\Request;

class AccessLogsController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.superadmin');
    }

    /**
     * Displays daily logs.
     *
     * @param Request         $request
     * @param AccessLogEngine $accessLogEngine
     *
     * @return mixed
     */
    public function overview(Request $request, AccessLogEngine $accessLogEngine)
    {
        $selectedUsers = $request->get('users', []);
        $logs = $accessLogEngine->get(appId(), $selectedUsers);
        $users = [];
        $logs->each(function ($log) use (&$users) {
            if (! isset($users[$log->user_id])) {
                $users[$log->user_id] = $log->user;
            }
        });

        return view('access-logs.list', [
            'logs' => $logs,
            'users' => $users,
            'selectedUsers' => $selectedUsers,
        ]);
    }
}
