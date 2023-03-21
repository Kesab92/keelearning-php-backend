<?php

namespace App\Providers;

use App\Http\Middleware\ConcurrentMiddleware;
use App\Models\App;
use App\Push\Deepstream;
use App\Services\AppSettings;
use App\Services\MorphTypes;
use Auth;
use Carbon\Carbon;
use Config;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Tymon\JWTAuth\Http\Parser\AuthHeaders;
use Tymon\JWTAuth\Http\Parser\Parser;
use URL;
use Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $request = $this->app['request'];

        // Trust all proxies - proxy is whatever
        // the current client IP address is
        $proxies = [$request->getClientIp()];
        $request->setTrustedProxies($proxies, config('trustedproxy.headers'));
        \Symfony\Component\HttpFoundation\Request::setTrustedProxies($proxies, config('trustedproxy.headers'));

        if (env('APP_ENV') === 'production') {
            URL::forceScheme('https');
        }

        // Setup carbon
        Carbon::setLocale(Config::get('app.locale'));

        // Setup the sidenav
        view()->composer('layout.partials.sidenav', function ($view) {
            $view->with('settings', app(AppSettings::class));
            $view->with('apps', App::all());
            $view->with('user', Auth::user());
        });

        // Setup the "main stats view"
        view()->composer('dashboard.partials.main-stats', function ($view) {
            $view->with('settings', app(AppSettings::class));
        });

        // Add a csv rule that only checks the filename ending
        Validator::extend('csv', function ($attribute, $value, $parameters) {
            return $value->getClientOriginalExtension() == 'csv';
        }, 'Die Datei muss eine .csv Datei sein');

        // Setup the semantic ui pagination view
        Paginator::defaultView('pagination::default');

        Relation::morphMap(array_flip(MorphTypes::MAPPING));

        $this->setupJWT();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(AppSettings::class, function () {
            return new AppSettings();
        });
        $this->app->singleton(ConcurrentMiddleware::class);
    }

    public function setupJWT()
    {
        $this->app->extend('tymon.jwt.parser', function (Parser $parser, $app) {
            $chain = $parser->getChain();
            foreach ($chain as $entry) {
                if ($entry instanceof AuthHeaders) {
                    $entry->setHeaderName('x-quizapp-authorization');
                }
            }
            return $parser;
        });
    }
}
