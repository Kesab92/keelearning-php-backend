<?php

namespace App\Http\Controllers\Api;

use App\Http\APIError;
use App\Http\Controllers\Controller;
use App\Models\AuthToken;
use App\Responses\LoginResponse;
use App\Services\AuthEngine;
use App\Services\Users\TokenLoginEngine;
use App\Services\Users\TokenLoginException;
use Exception;
use Illuminate\Http\Request;

class TmpTokenLoginController extends Controller
{
    public function getTmpToken()
    {
        $tokenLoginEngine = app(TokenLoginEngine::class);
        return response()->json([
            'tmpToken' =>$tokenLoginEngine->createLoginToken(user(), 30),
        ]);
    }

    public function tokenLogin(Request $request)
    {
        $tokenLoginEngine = app(TokenLoginEngine::class);
        try {
            $user = $tokenLoginEngine->getUserFromToken($request->input('token'));
        } catch (TokenLoginException $e) {
            return new APIError($e->getMessage(), 401);
        }
        try {
            if (!$token = auth('api')->login($user)) {
                return new APIError(__('errors.invalid_login_data'), 401);
            }
        } catch (Exception $e) {
            return new APIError(__('errors.could_not_create_token'), 401);
        }

        $user = auth('api')->setToken($token)->user();

        $deletedTokens = (new AuthEngine)->updateAuthTokens($user, $token);

        return new LoginResponse($user, $token, $deletedTokens);
    }
}
