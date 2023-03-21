<?php

namespace App\Services\Users;

use App\Http\APIError;
use App\Models\AppProfile;
use App\Models\User;
use Cache;
use Str;

class TokenLoginEngine
{
    // Cache Lifetime in Seconds
    const DEFAULT_LOGIN_TOKEN_LIFETIME = 10 * 60;

    public function createLoginToken(User $user, $tokenLifetime = null)
    {
        if(!$tokenLifetime) {
            $tokenLifetime = self::DEFAULT_LOGIN_TOKEN_LIFETIME;
        }
        $loginToken = $this->createRandomToken();
        Cache::put('logintoken:' . $loginToken, $user->id, $tokenLifetime);
        return $loginToken;
    }

    /**
     * Fetches the user from the token, given they are able to login afterwards
     *
     * @param string $loginToken
     * @return User
     * @throws TokenLoginException
     */
    public function getUserFromToken(string $loginToken): User
    {
        $userId = Cache::get('logintoken:' . $loginToken);
        if (!$userId) {
            throw new TokenLoginException(__('errors.invalid_login_data'));
        }
        Cache::forget('logintoken:' . $loginToken);
        $user = User::find($userId);
        if (!$user) {
            throw new TokenLoginException(__('errors.invalid_login_data'));
        }
        if (!is_null($user->deleted_at)) {
            throw new TokenLoginException(__('errors.user_deleted'));
        }
        if ($user->active == 0 || $user->is_dummy) {
            throw new TokenLoginException(__('errors.user_inactive'));
        }

        return $user;
    }

    public function createRandomToken()
    {
        // same logic as single use token generation from
        // Illuminate/Auth/Passwords/PasswordBrokerManage
        $key = config('app.key');
        if (Str::startsWith($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }
        return hash_hmac('sha256', Str::random(40), $key);
    }
}
