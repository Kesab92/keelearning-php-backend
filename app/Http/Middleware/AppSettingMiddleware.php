<?php

namespace App\Http\Middleware;

use App\Services\AppSettings;
use Closure;
use Session;

class AppSettingMiddleware
{
    /**
     * Only allows access if a certain app setting is set
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param $setting Checks if the given app setting is truthy
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle($request, Closure $next, string $setting)
    {
        if ((new AppSettings(appId()))->getValue($setting)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 403);
        } else {
            Session::flash('error-message', 'Sie haben dazu leider keine Berechtigung!');
            return redirect()->to('/');
        }
    }
}
