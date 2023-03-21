<?php

namespace App\Exceptions;

use App\Http\APIError;
use Fruitcake\Cors\CorsService;
use Cache;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Foundation\Http\Exceptions\MaintenanceModeException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthenticationException::class,
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        TokenMismatchException::class,
        ValidationException::class,
        TokenInvalidException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param \Exception $e
     *
     * @return void
     * @throws \Throwable
     */
    public function report(Throwable $e)
    {
        if (app()->bound('sentry') && $this->shouldReport($e)) {
            /** @var \Sentry $sentry */
            $sentry = app('sentry');
            $sentry->captureException($e);
        }
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception               $e
     *
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $e)
    {
        // If this is an API request we want to add the CORS headers and return a properly formatted error
        if ($request->is('api/*')) {
            if ($e instanceof ValidationException) {
                $errors = collect($e->errors())->map(function ($item) {
                    return implode('; ', $item);
                })->toArray();

                $response = new APIError(implode(', ', array_values($errors)), $e->status);
            } elseif ($e instanceof MaintenanceModeException) {
                $response = new APIError('Wartungsarbeiten - Die App steht in Kürze wieder zur Verfügung.', 503);
            } else {
                // Decide what message to show the user
                $code = 500;
                if ($e instanceof HttpException) {
                    $code = $e->getStatusCode();
                }

                if($e instanceof AuthenticationException) {
                    $response = new APIError('Sie sind leider nicht berechtigt diese Aktion auszuführen.', 401);
                } elseif($e instanceof ThrottleRequestsException) {
                    $response = new APIError('Sie haben zu viele Anfragen gesendet. Bitte warten Sie, bevor Sie weitere Anfragen schicken', 429);
                } else {
                    if (live()) {
                        $response = new APIError('Ein unerwarteter Fehler ist aufgetreten. Bitte laden Sie die Anwendung neu.', $code);
                    } else {
                        $response = new APIError($e->getMessage(), $code);
                    }
                }
            }

            // Add the CORS headers
            app(CorsService::class)->addActualRequestHeaders($response, $request);

            return $response;
        }

        // regular request? check if we need to redirect an old app to a new asset url
        // make sure all 404 hits for assets get routed to the app (nginx: `try_files $uri $uri/ /index.php;` in asset handling)
        // TODO: downloading & streaming instead of redirecting?
        $storagePath = $request->getPathInfo();
        $appUrl = env('APP_URL');
        $storagePath = preg_replace('~^/storage/'.$appUrl.'(/storage)?~', '', $storagePath);
        $storagePath = preg_replace('~^/storage/~', '', $storagePath);
        $storagePath = preg_replace('~^/laravel-file-storage-prod/~', '', $storagePath);
        $storagePath = preg_replace('~^/~', '', $storagePath);
        $newUrl = Cache::get('upload-'.$storagePath);
        if (! $newUrl && Storage::exists($storagePath)) {
            $newUrl = Storage::url($storagePath);
            if ($newUrl) {
                Cache::forever('upload-'.$storagePath, $newUrl);
            }
        }
        if ($newUrl) {
            $response = redirect($newUrl, 301);
            app(CorsService::class)->addActualRequestHeaders($response, $request);

            return $response;
        }

        return parent::render($request, $e);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param AuthenticationException $e
     *
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $e)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        } else {
            return redirect()->guest('login');
        }
    }
}
