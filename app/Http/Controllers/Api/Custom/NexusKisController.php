<?php

namespace App\Http\Controllers\Api\Custom;

use App\Http\APIError;
use App\Http\Controllers\Controller;
use App\Models\AnalyticsEvent;
use App\Models\App;
use App\Models\User;
use App\Services\IPGeolocation;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as Input;
use Response;
use Tymon\JWTAuth\Exceptions\JWTException;

class NexusKisController extends Controller
{
    public function login(Request $request)
    {
        $this->validate($request, [
          'username'  => 'required|min:2|max:255',
          'firstname' => 'required',
          'lastname'  => 'required',
        ]);

        $app = App::findOrFail(App::ID_NEXUS_KIS);
        $password = $request->get('username');

        $existingUser = User::where('app_id', $app->id)->where('username', $request->get('username'))->first();
        if (! $existingUser) {
            $existingUser = new User();
            $existingUser->active = true;
            $existingUser->app_id = $app->id;
            $existingUser->email = uniqid().'@localhost';
            $existingUser->language = language($app->id);
            $existingUser->username = Input::get('username');
            $existingUser->password = Hash::make($password);
            $existingUser->tos_accepted = true;
            $existingUser->save();
            $existingUser->setMeta('firstname', $request->get('firstname'));
            $existingUser->setMeta('lastname', $request->get('lastname'));

            AnalyticsEvent::log($existingUser, AnalyticsEvent::TYPE_USER_CREATED);
        }

        try {
            // attempt to verify the credentials and create a token for the user
            if (! $token = auth('api')->attempt([
                'email'    => $existingUser->getRawOriginal('email'),
                'password' => $password,
                'app_id'   => $app->id,
            ])) {
                return new APIError(__('errors.invalid_login_data'), 401);
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return new APIError(__('errors.could_not_create_token'), 401);
        }

        // first check if the user is active. if all is good return the token, id and username
        $user = auth('api')->setToken($token)->user();

        if (! is_null($user->deleted_at)) {
            return new APIError(__('errors.user_deleted'));
        }

        if ($user->active == 0) {
            return new APIError(__('errors.user_inactive'));
        }

        $appSettings = app(\App\Services\AppSettings::class);
        $user->language = language();
        if ($appSettings->getValue('save_user_ip_info')) {
            $user->country = IPGeolocation::getInstance()->isoCode($request->ip());
        }
        $user->save();

        $response = [
            'token'        => $token,
            'id'           => $user->id,
            'name'         => $user->username,
            'tos_accepted' => $user->tos_accepted,
            'email'        => $user->email,
            'avatar'       => $user->avatar_url,
            'success'      => true,
        ];

        return Response::json($response);
    }
}
