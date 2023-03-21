<?php

namespace App\Http\Middleware;

use App\Services\AppSettings;
use Auth;
use Closure;
use Session;

class SuperadminMiddleware
{
    /**
     * Check the user's admin rights.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @param                           $area
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! $request->user()->isSuperAdmin()) {
            Auth::logout();
            Session::flush();
            Session::flash('error-message', 'Sie haben dazu leider keine Berechtigung!');

            return redirect()->to('/login');
        }

        return $next($request);
    }
}
