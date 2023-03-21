<?php

namespace App\Http\Middleware;

use Closure;
use Cache;
use Illuminate\Http\Exceptions\ThrottleRequestsException;

// this middleware must be registered as a singleton!
class ConcurrentMiddleware
{
    protected $cacheForSeconds = 10;
    protected $allowedRequest = false;

    public function handle($request, Closure $next)
    {
        if (Cache::has($this->getSignature($request))) {
            throw new ThrottleRequestsException('No concurrent requests allowed.', null);
        }
        $this->allowedRequest = true;
        Cache::put($this->getSignature($request), true, $this->cacheForSeconds);
        return $next($request);
    }

    public function terminate($request, $response)
    {
        if ($this->allowedRequest) {
            Cache::forget($this->getSignature($request));
        }
        return $response;
    }

    public function getSignature($request)
    {
        return 'concurrent:' . sha1($request->route()->uri() . '|' . serialize($request->input()) . '|' . $request->ip());
    }
}
