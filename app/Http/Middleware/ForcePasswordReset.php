<?php

namespace App\Http\Middleware;

use App\Http\APIError;
use Closure;
use Illuminate\Http\Request;

class ForcePasswordReset
{
    protected $headers = [
        'x-logout-user' => true,
    ];

    protected $routesWithoutTos = [
        'api/v1/learning',
    ];
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = null;
        try {
            $user = user();
        } catch (\Exception $e) {
            \Sentry::captureException($e);
        }
        if(!$user) {
            return new APIError(__('errors.user_does_not_exist'), 401, $this->headers);
        }

        if ($user->force_password_reset && $this->shouldDenyDueToPasswordReset($request)) {
            return new APIError(__('errors.password_not_reset', [], $user->getLanguage()), 401, [
                'x-password-not-reset' => true,
            ]);
        }
        return $next($request);
    }

    private function shouldDenyDueToPasswordReset(Request $request)
    {
        foreach ($this->routesWithoutTos as $exception) {
            if ($request->is($exception)) {
                return false;
            }
        }

        // Only deny access if the frontend can handle it (redirect to the password reset page)
        $apiVersion = request()->header('X-API-VERSION', '1.0.0');

        return version_compare($apiVersion, '2.1.0', '>=');
    }
}
