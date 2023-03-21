<?php

namespace App\Http;

use App\Http\Middleware\ActiveMiddleware;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\AllowBackendRelaunchAccess;
use App\Http\Middleware\ApiAdminMiddleware;
use App\Http\Middleware\AppSettingMiddleware;
use App\Http\Middleware\BackendAccessMiddleware;
use App\Http\Middleware\ConcurrentMiddleware;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\ForcePasswordReset;
use App\Http\Middleware\MainadminMiddleware;
use App\Http\Middleware\PreventRequestsDuringMaintenance;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\SetLocaleMiddleware;
use App\Http\Middleware\SuperadminMiddleware;
use App\Http\Middleware\TrustProxies;
use App\Http\Middleware\VerifyCsrfToken;
use Fruitcake\Cors\HandleCors;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequestsWithRedis;
use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        TrustProxies::class,
        HandleCors::class,
        PreventRequestsDuringMaintenance::class,
        AllowBackendRelaunchAccess::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
        ],

        'api' => [
            'setlocale' => SetLocaleMiddleware::class,
            SubstituteBindings::class,
            'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        ],

        'backend-api' => [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
            'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        ],

        'public-api' => [],

        'xapi' => [],

        'admin' => [
            'auth'       => Authenticate::class,
            'auth.admin' => AdminMiddleware::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'can'                => Authorize::class,
        'concurrent'         => ConcurrentMiddleware::class,
        'guest'              => RedirectIfAuthenticated::class,
        'throttle'           => ThrottleRequestsWithRedis::class,
        'auth'               => Authenticate::class,
        'auth.active'        => ActiveMiddleware::class,
        'auth.appsetting'    => AppSettingMiddleware::class,
        'auth.resetpassword' => ForcePasswordReset::class,
        'auth.basic'         => AuthenticateWithBasicAuth::class,
        'auth.backendaccess' => BackendAccessMiddleware::class,
        'auth.mainadmin'     => MainadminMiddleware::class,
        'auth.superadmin'    => SuperadminMiddleware::class,
        'auth.apiadmin'      => ApiAdminMiddleware::class,
        'signed'             => ValidateSignature::class,
    ];
}
