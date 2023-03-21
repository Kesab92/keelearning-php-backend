<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Str;
use Symfony\Component\HttpFoundation\Response;

class AllowBackendRelaunchAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allowedFrameAncestor = 'http://qa.test:*';
        if(live()) {
            $allowedFrameAncestor = 'https://myadmin.staging.keelearning.de https://myadmin.keelearning.de https://admin.keelearning.de https://admin.staging.keelearning.de';
        }
        if(env('APP_ENV') !== 'testing') {
            header('Content-Security-Policy: frame-ancestors ' . $allowedFrameAncestor);
        }

        return $next($request);
    }
}
