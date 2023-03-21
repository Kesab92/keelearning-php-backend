<?php

namespace App\Http\Middleware;

use Closure;
use Session;

class MainadminMiddleware
{
    public function handle($request, Closure $next)
    {
        if($request->user()->isMainAdmin()) {
            return $next($request);
        }
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        } else {
            Session::flash('error-message', 'Sie haben dazu leider keine Berechtigung!');
            return redirect()->to('/');
        }
    }
}
