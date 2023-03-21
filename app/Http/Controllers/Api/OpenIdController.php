<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppProfile;
use App\Services\OpenIdEngine;
use App\Services\Users\TokenLoginEngine;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Response;

class OpenIdController extends Controller
{
    /**
     * Returns the URL the client's browser has to open
     * to do the OpenID Connect verification.
     *
     * @param int $profileId ID of app profile
     * @param string|null $nativeAppId ID of native app if any, eg `de.sopamo.keeunit.keelearning`
     * @return JsonResponse
     */
    public function getAuthUrl(int $profileId, ?string $nativeAppId = null)
    {
        $appProfile = AppProfile::where('id', $profileId)->firstOrFail();
        $openIdEngine = new OpenIdEngine($appProfile);
        $authUrl = $openIdEngine->getAuthUrl($nativeAppId);
        return Response::json([
            'authUrl' => $authUrl,
        ]);
    }

    public function receiveToken(Request $request)
    {
        $response = $request->all();
        parse_str(base64_decode($response['state']), $state);
        $appProfile = AppProfile::where('id', $state['profile_id'])->firstOrFail();
        $nativeAppId = $state['native_app_id'] ?? null;

        if (isset($response['error'])) {
            return header('Location: '.$appProfile->getAppDeepLink('/auth/login/openIdError/'.base64_encode($response['error']), $nativeAppId));
        }

        $openIdEngine = new OpenIdEngine($appProfile);
        $tokenLoginEngine = new TokenLoginEngine();
        // https://docs.microsoft.com/en-us/azure/active-directory/develop/id-tokens
        try {
            $idToken = $openIdEngine->decodeJwt($response['id_token']);
        } catch (Exception $e) {
            return header('Location: '.$appProfile->getAppDeepLink('/auth/login/openIdError/'.base64_encode($e->getMessage()), $nativeAppId));
        }

        if (!$openIdEngine->getIdentifierFromToken($idToken)) {
            return header('Location: '.$appProfile->getAppDeepLink('/auth/login/openIdError/'.base64_encode('no valid identifier in token'), $nativeAppId));
        }

        $user = $openIdEngine->getUserFromToken($idToken);
        if (!$user) {
            if (!$appProfile->getValue('enable_sso_registration')) {
                return header('Location: '.$appProfile->getAppDeepLink('/auth/login/openIdError/'.base64_encode(__('errors.sso_registration_disabled', ['ssoapp' => $appProfile->getValue('openid_title')])), $nativeAppId));
            }
            $user = $openIdEngine->createUserFromToken($idToken);
        }
        if (!$user) {
            return header('Location: '.$appProfile->getAppDeepLink('/auth/login/openIdError/'.base64_encode('no valid user'), $nativeAppId));
        }

        // The users are only allowed to login to their app profile
        if($user->getAppProfile()->id !== $appProfile->id) {
            return header('Location: '.$appProfile->getAppDeepLink('/auth/login/openIdError/'.base64_encode(__('errors.sso_app_profile_mismatch')), $nativeAppId));
        }

        $loginToken = $tokenLoginEngine->createLoginToken($user);
        return header('Location: '.$appProfile->getAppDeepLink('/auth/openId/'.$loginToken, $nativeAppId));
    }

    public function tokenLogin(Request $request)
    {
        $appProfile = AppProfile::where('id', $request->input('profileId'))->firstOrFail();
        $openIdEngine = new OpenIdEngine($appProfile);
        return $openIdEngine->doTokenLogin($request->input('token'));
    }
}
