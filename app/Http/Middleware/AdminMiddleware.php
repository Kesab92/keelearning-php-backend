<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Session;

class AdminMiddleware
{
    /**
     * Check the user's admin rights.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!Auth::user()->canAccessBackend()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            } else {
                Auth::logout();
                Session::flush();
                Session::flash('error-message', 'Sie sind leider kein Administrator und dazu nicht berechtigt!');
                return redirect()->to('/login');
            }
        }

        return $next($request);
    }
}
