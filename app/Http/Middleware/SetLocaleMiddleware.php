<?php

namespace App\Http\Middleware;

use App;
use App\Http\APIError;
use Auth;
use Closure;
use Config;
use Illuminate\Support\Str;
use Request;
use Session;

class SetLocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            $user = null;
            try {
                $user = user();
            } catch (\Exception $e) {
            }

            if ($user !== null) {
                App::setLocale($user->getLanguage());
            } else {
                if ($xLanguage = request()->header('X-LANGUAGE')) {
                    App::setLocale($xLanguage);
                }
            }
        } catch (\Exception $e) {
            if (! live()) {
                throw $e;
            }
        }

        return $next($request);
    }
}
