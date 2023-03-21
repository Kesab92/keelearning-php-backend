<?php

namespace App\Http\Controllers\Backend\Auth;

use App\Http\Controllers\Controller;
use App\Mail\Mailer;
use App\Models\AccessLog;
use App\Models\App;
use App\Models\User;
use App\Push\Deepstream;
use App\Services\AccessLogEngine;
use App\Services\IPGeolocation;
use Config;
use Cookie;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request as Input;
use Redirect;
use Response;
use Session;
use Validator;

class AuthController extends Controller
{
    /**
     * The function returns the login view.
     *
     * @return mixed
     */
    public function getLogin()
    {
        return view()->make('login.login');
    }

    /**
     * The function returns the password reset view.
     *
     * @return mixed
     */
    public function getPasswordReset()
    {
        return view()->make('login.forgot-password');
    }

    /**
     * Reset the password for a user (at a specific app) and send an email.
     *
     * @return mixed
     */
    public function postPasswordReset(Request $request, Mailer $mailer)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        // Check if the email is valid
        if ($validator->fails()) {
            Session::flash('error-message', __('errors.invalid_mail'));

            return redirect('/password-reset');
        }

        $email = $request->get('email');
        $user = User::whereEmail($email)
            ->where('app_id', $request->get('appid'))
            ->first();

        // Check if there is a user with this email
        if ($user == null) {
            Session::flash('error-message', __('errors.no_user_with_mail'));

            return redirect('/password-reset');
        }

        if (! is_null($user->deleted_at)) {
            Session::flash('error-message', __('errors.user_deleted'));

            return redirect('/password-reset');
        }

        // Check if the user is active
        if ($user->active == 0 || $user->is_dummy || $user->is_api_user) {
            Session::flash('error-message', __('errors.user_inactive'));

            return redirect('/password-reset');
        }

        // Get a new password, save it and send it via email
        $password = randomPassword();
        $mailer->sendBackendResetEmail($user, $password);

        $user->password = Hash::make($password);
        $user->save();

        Session::flash('success-message', 'Eine Mail mit dem neuen Passwort wurde versendet!');

        return redirect('/login');
    }

    public function getApps()
    {
        $email = Input::get('email');
        if (! $email) {
            return Response::json([]);
        }
        $appIds = User::where('email', $email)
            ->where('is_dummy', false)
            ->where('is_api_user', false)
            ->where('is_admin', 1)
            ->where('active', 1)
            ->pluck('app_id');
        if (! $appIds) {
            return Response::json([]);
        }
        $apps = App::whereIn('id', $appIds)->pluck('name', 'id');

        return Response::json($apps);
    }

    /**
     * The function attempts to login the user.
     *
     * @param Request $request
     */
    public function postLogin(Request $request, AccessLogEngine $accessLogEngine)
    {
        $credentials = [
            'email'    => $request->get('email'),
            'password' => $request->get('password'),
            'app_id'   => $request->get('appid'),
        ];

        $remember = $request->get('remember');

        // If the user is already logged in or the login with credentials passes
        if (Auth::check() || Auth::attempt($credentials, $remember)) {
            $user = Auth::user();
            if (!$user->canAccessBackend()) {
                Auth::logout();
                Session::flush();
                Session::flash('error-message', 'Ihre Anmeldedaten stimmen leider nicht!');
                return redirect('/login');
            }
            $appSettings = app(\App\Services\AppSettings::class);

            if ($appSettings->getValue('save_user_ip_info')) {
                $user->country = IPGeolocation::getInstance()
                                              ->isoCode($request->ip());
                $user->save();
            }
            $accessLogEngine->log(AccessLog::ACTION_LOGIN);

            return redirect('/');
        } else {
            Session::flash('error-message', 'Ihre Anmeldedaten stimmen leider nicht!');
            return redirect('/login')->withInput($request->except('password'));
        }
    }

    /**
     * The function logs the user out and redirects to the login page.
     *
     * @param Request $request
     * @return mixed
     */
    public function getLogout(Request $request)
    {
        Auth::logout();
        Session::flush();

        return redirect('/login');
    }

    /**
     * Switches the language.
     *
     * @param $newLang
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function setlang($newLang, Request $request)
    {
        // Setting the language in the old backend is disabled due to the relaunch
        return Redirect::back();
        // Create the cookie with the selected app id
        $cookie = Cookie::forever('lang', $newLang, null, null, Config::get('session.secure'), Config::get('session.http_only'));

        if ($newLang == defaultAppLanguage()) {
            Session::flash('lang-message', 'Bearbeitungsmodus auf Primärsprache '.__('general.lang_'.$newLang).' eingestellt');
        } else {
            Session::flash('lang-message', 'Bearbeitungsmodus auf Sekundärsprache '.__('general.lang_'.$newLang).' eingestellt');
        }

        // Redirect the user back with the new cookie
        if ($request->has('redirect')) {
            $response = Redirect::to($request->input('redirect'));
        } else {
            $response = Redirect::back();
        }
        $response->withCookie($cookie);

        return $response;
    }

    /**
     * Activates the given account.
     *
     * @param Request $request
     * @param $userId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function activateAccount(Request $request, $userId)
    {
        if (! $request->hasValidSignature()) {
            abort(401);
        }

        /** @var User $user */
        $user = User::findOrFail(($userId));
        $user->active = 1;
        $user->save();

        try {
            /** @var Deepstream $deepstream */
            $deepstream = new Deepstream($user->getApp());
            $deepstream->sendEvent('users/'.$user->id.'/activated');
        } catch (\Exception $e) {
            // It's okay to fail silently here
        }

        $appProfile = $user->getAppProfile();

        return Redirect::to('https://' . $appProfile->getDomain());
    }
}
