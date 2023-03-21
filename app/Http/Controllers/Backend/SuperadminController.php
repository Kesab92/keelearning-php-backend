<?php

namespace App\Http\Controllers\Backend;

use App\Exports\DefaultExport;
use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\User;
use App\Services\MorphTypes;
use App\Services\Users\UserActivityExport;
use Carbon\CarbonImmutable;
use Config;
use Cookie;
use DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View as IlluminateView;
use Laravel\Sanctum\PersonalAccessToken;
use Maatwebsite\Excel\Facades\Excel;
use Redirect;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use View;

class SuperadminController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.superadmin');
    }

    public function appswitcher(): IlluminateView
    {
        $appUsers = User
                ::where('tos_accepted', 1)
                ->whereNull('deleted_at')
                ->select([DB::raw('COUNT(*) as c'), 'app_id'])
                ->groupBy('app_id')
                ->pluck('c', 'app_id');

        $appAuthTokens = PersonalAccessToken
            ::join('users', 'users.id', '=', 'personal_access_tokens.tokenable_id')
            ->where('users.is_api_user', 1)
            ->where('personal_access_tokens.tokenable_type', MorphTypes::TYPE_USER)
            ->select([DB::raw('COUNT(*) as c'), 'app_id'])
            ->groupBy('app_id')
            ->pluck('c', 'app_id');

        $appMainAdmins = User::whereNull('deleted_at')
            ->select([DB::raw('COUNT(*) as c'), 'app_id'])
            ->where('is_admin', true)
            ->whereHas('role', function ($query) {
                $query->where('is_main_admin', true);
            })
            ->groupBy('app_id')
            ->pluck('c', 'app_id');

        $relevantColumns = [
            'id',
            'created_at',
            'user_licences',
            'internal_notes',
        ];

        $apps = App
            ::select($relevantColumns)
            ->with('profiles.settings')
            ->get()
            ->map(function(App $app) use ($appAuthTokens, $appMainAdmins, $appUsers, $relevantColumns) {
                $data = $app->only([...$relevantColumns, 'app_name', 'logo_url']);
                $data['app_hosted_at'] = $app
                    ->getDefaultAppProfile()
                    ->app_hosted_at;
                $data['registered_users'] = $appUsers->get($app->id);
                $data['main_admins'] = $appMainAdmins->get($app->id);
                $data['auth_tokens'] = $appAuthTokens->get($app->id);
                return $data;
            });
        return view('vue-component', [
            'hasFluidContent' => false,
            'component' => 'routed',
            'props' => [
                'apps' => $apps,
            ],
        ]);
    }

    /**
     * Switches the app.
     *
     * @param $newApp
     *
     * @return RedirectResponse
     */
    public function setapp($newApp): RedirectResponse
    {
        echo "Setting the active app in the old backend is not supported anymore.";
        exit;
        // Redirect the user back with the new cookie
        return Redirect::to('/')
            ->withCookie(Cookie::forever('appid', $newApp, null, null, Config::get('session.secure'), Config::get('session.http_only')))
            ->withCookie(Cookie::forever('lang', defaultAppLanguage($newApp), null, null, Config::get('session.secure'), Config::get('session.http_only')));
    }

    /**
     * Date range selector for superadmin user activity stats
     *
     * @return View
     */
    public function userActivity(): IlluminateView
    {
        View::share('activeNav', 'superadmin.useractivity');
        return view('vue-component', [
            'hasFluidContent' => false,
            'component' => 'routed',
        ]);
    }

    /**
     * Downloads the user activity stats
     *
     * @return BinaryFileResponse
     */
    public function userActivityDownload(): BinaryFileResponse
    {
        set_time_limit(60 * 5);
        $from = CarbonImmutable::createFromFormat('Y-m-d', request()->get('from'));
        $to = CarbonImmutable::createFromFormat('Y-m-d', request()->get('to'));
        $userActivityExport = new UserActivityExport($from, $to);
        return Excel::download(new DefaultExport($userActivityExport->get(), 'csv-export'), 'user-activity-'.$from->toDateString().'-to-'.$to->toDateString().'.xlsx');
    }
}
