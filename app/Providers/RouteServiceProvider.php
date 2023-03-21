<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to the controller routes in your routes file.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * The path to the "home" route for your application.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        Route::pattern('game_id', '[0-9]+');
        Route::pattern('id', '[0-9]+');
        $this->configureRateLimiting();

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();
        $this->mapBackendApiRoutes();
        $this->mapWebRoutes();
        $this->mapXapiRoutes();
        $this->mapPublicApiRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::group([
                'middleware' => 'web',
                'namespace'  => $this->namespace,
        ], function ($router) {
            require base_path('routes/web.php');
        });
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::group([
                'middleware' => 'api',
                'namespace'  => $this->namespace,
                'prefix'     => 'api/v1',
        ], function ($router) {
            require base_path('routes/api.php');
        });
    }

    /**
     * Define the "api" routes for the admin backend.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapBackendApiRoutes()
    {
        Route::group([
                'middleware' => 'backend-api',
                'namespace'  => $this->namespace.'\BackendApi',
                'prefix'     => 'backend/api/v1',
        ], function ($router) {
            require base_path('routes/backend-api.php');
        });
    }

    /**
     * Define the "xapi" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapXapiRoutes()
    {
        Route::group([
                'middleware' => 'xapi',
                'namespace'  => $this->namespace.'\XApi',
                'prefix'     => 'xapi',
        ], function ($router) {
            require base_path('routes/xapi.php');
        });
    }

    /**
     * Define the "api" routes for the public API.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapPublicApiRoutes()
    {
        Route::group([
            'middleware' => 'public-api',
            'namespace'  => $this->namespace.'\PublicApi',
            'prefix'     => 'api/public/v1',
        ], function ($router) {
            require base_path('routes/public-api.php');
        });
    }

    /**
     * Checks, if the ip from the request is excempted from throttling
     */
    private function isAllowlisted(Request $request) {
        $ipAllowlist = collect(explode(',', env('THROTTLE_IP_ALLOWLIST')));
        $appAllowList = collect(explode(',', env('THROTTLE_APP_ALLOWLIST')));
        if(user()) {
            if($appAllowList->contains(user()->app_id)) {
                return true;
            }
        }
        return $ipAllowlist->contains($request->ip());
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('frontendAuth', function (Request $request) {
            if($this->isAllowlisted($request)) {
                return Limit::none();
            }
            return Limit::perMinute(150)->by($request->ip());
        });

        RateLimiter::for('frontend', function (Request $request) {
            if($this->isAllowlisted($request)) {
                return Limit::none();
            }
            return Limit::perMinute(200)->by(user() ? user()->id : $request->ip());
        });
    }
}
