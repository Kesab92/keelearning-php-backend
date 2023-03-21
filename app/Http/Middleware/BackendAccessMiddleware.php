<?php

namespace App\Http\Middleware;

use App\Services\AppSettings;
use Closure;
use Session;

class BackendAccessMiddleware
{
    /**
     * Check the user's admin role's rights.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param $area If given, will check if the backend area is visible
     * @param $rights Check against a pipe-separated list of rights, needs to have at least one
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle($request, Closure $next, $area, $rights)
    {
        if ($request->user()->isSuperAdmin()) {
            return $next($request);
        }
        /** @var AppSettings $settings */
        $settings = new AppSettings($request->user()->app_id);
        $appHasAccess = true;
        if ($area && !$settings->isBackendVisible($area)) {
            $appHasAccess = false;
        }
        if($appHasAccess) {
            foreach(explode('|', $rights) as $right) {
                if($request->user()->hasRight($right)) {
                    return $next($request);
                }
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 403);
        } else {
            Session::flash('error-message', 'Sie haben dazu leider keine Berechtigung!');
            return redirect()->to('/');
        }
    }
}
