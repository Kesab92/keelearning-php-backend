<?php

namespace App\Http\Controllers\Backend;

use App\Exports\DefaultExport;
use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\Tag;
use App\Models\User;
use App\Services\UserEngine;
use App\Traits\PersonalData;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use View;

class UsersController extends Controller
{
    use PersonalData;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:,users-edit|users-view')
            ->except(['redirectToAvatar']);
        $this->middleware('auth.backendaccess:user_export,users-export')
            ->only(['export']);
        $this->middleware('auth.backendaccess:,dashboard-userdata')
            ->only(['redirectToAvatar']);
        $this->personalDataRightsMiddleware('users');
        View::share('activeNav', 'users');
    }

    public function index(Request $request)
    {
        return view('vue-component', [
            'hasFluidContent' => false,
            'component' => 'routed',
            'props' => [
                'tags' => Tag::ofApp(appId())->get(),
                'readonly' => !$request->user()->hasRight('users-edit'),
                'can-export' => $this->appSettings->isBackendVisible('user_export') && $request->user()->hasRight('users-export'),
            ],
        ]);
    }

    /**
     *  Exports all users.
     */
    public function export(UserEngine $userEngine)
    {
        $app = App::findOrFail(appId());

        $filename = 'user-export-'.Carbon::now()->format('d.m.Y-H:i').'.xlsx';

        $search = request()->get('search');
        $tags = request()->get('tags');
        if ($tags) {
            $tags = explode(',', $tags);
        }
        $filter = request()->get('filter');
        $orderBy = request()->get('sortBy', 'id');
        $orderDescending = (bool) (request()->get('descending') === 'true');

        $users = $userEngine
            ->userFilterQuery(appId(), $search, $tags, $filter, $orderBy, $orderDescending, $this->showPersonalData, $this->showEmails)
            ->with('metafields')
            ->get();

        $metaFields = $app->getUserMetaDataFields($this->showPersonalData);

        $data = [
            'users' => $users,
            'showEmails' => $this->showEmails,
            'showPersonalData' => $this->showPersonalData,
            'metaFields' => $metaFields,
        ];

        return Excel::download(new DefaultExport($data, 'users.csv.export'), $filename);
    }

    public function redirectToAvatar($userId)
    {
        $user = User::ofApp(appId())
            ->withoutGlobalScope('human')
            ->findOrFail($userId);
        return redirect($user->avatar_url);
    }
}
