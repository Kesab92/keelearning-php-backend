<?php

namespace App\Http\Middleware;

use App\Exceptions\Sentry;
use App\Http\APIError;
use App\Services\AuthEngine;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ActiveMiddleware
{
    protected $headers = [
        'x-logout-user' => true,
    ];

    protected $routesWithoutTos = [
        'api/v1/tos',
        'api/v1/accept-tos',
        'api/v1/learning',
        'api/v1/setfcmid',
        'api/v1/setgcmid',
        'api/v1/setgcmauth',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = null;
        try {
            $user = user();
        } catch (\Exception $e) {
            \Sentry::captureException($e);
        }

        if (!$user) {
            return new APIError(__('errors.user_does_not_exist'), 401, $this->headers);
        }

        if (! is_null($user->deleted_at)) {
            return new APIError(__('errors.user_deleted', [], $user->getLanguage()), 401, $this->headers);
        }

        if ($user->active == 0) {
            return new APIError(__('errors.user_inactive', [], $user->getLanguage()), 401, $this->headers);
        }

        if ($user->tos_accepted == 0) {
            if ($this->shouldDenyDueToTOS($request)) {
                return new APIError(__('errors.tos_not_accepted', [], $user->getLanguage()), 401, [
                    'x-tos-not-accepted' => true,
                ]);
            }
        }

        if ((new AuthEngine)->getMaxConcurrentLogins($user)) {
            // FIXME: jwt-auth sometimes automatically overrides the old token with a new one?
            // some kind of magic inside auth('api')->getToken() makes testing impossible otherwise
            $token = utrim(substr($request->header('x-quizapp-authorization'), strlen('Bearer')));
            if (!$user->authTokens()->where('token', $token)->exists()) {
                return new APIError(__('errors.user_login_expired', [], $user->getLanguage()), 401, $this->headers);
            }
        }

        return $next($request);
    }

    private function shouldDenyDueToTOS(Request $request)
    {
        foreach ($this->routesWithoutTos as $exception) {
            if ($request->is($exception)) {
                return false;
            }
        }

        // Only deny access if the frontend can handle it (redirect to the tos page)
        $apiVersion = request()->header('X-API-VERSION', '1.0.0');

        return version_compare($apiVersion, '2.1.0', '>=');
    }
}
