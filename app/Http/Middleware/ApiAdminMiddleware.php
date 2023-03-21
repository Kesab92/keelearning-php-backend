<?php

namespace App\Http\Middleware;

use Closure;

class ApiAdminMiddleware
{
    /**
     * Check the user's admin rights.
     *
     * @param  \Illuminate\Http\Request $request
     * @param Closure|\Closure          $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! user()->is_admin) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthenticated.',
            ], 401);
        }

        return $next($request);
    }
}
