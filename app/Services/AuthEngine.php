<?php

namespace App\Services;

use App\Models\AuthToken;
use App\Models\User;

class AuthEngine
{
    /**
     * Adds a new auth token to a user, checking against app limits.
     *
     * @param User $user
     * @param string $token
     * @return integer|null returns the number of deleted old tokens, null if no delete rule is in place
     */
    public function updateAuthTokens(User $user, string $token): ?int
    {
        $authToken = new AuthToken;
        $authToken->user_id = $user->id;
        $authToken->token = $token;
        $authToken->save();

        $tokensToKeep = $user->authTokens()
            ->orderBy('id', 'DESC')
            ->limit($this->getMaxConcurrentLogins($user))
            ->pluck('id');
        return $user->authTokens()->whereNotIn('id', $tokensToKeep)->delete();
    }

    /**
     * Returns the currently active limit of concurrent logins, or null if no rule is active
     *
     * @param User $user
     * @return integer|null
     */
    public function getMaxConcurrentLogins(User $user): ?int
    {
        $appSettings = new AppSettings($user->app_id);
        if (!$appSettings->getValue('has_login_limiations')) {
            return null;
        }
        $appProfile = $user->getAppProfile();
        $maxConcurrentLogins = (int)$appProfile->getValue('max_concurrent_logins');
        if (!$maxConcurrentLogins) {
            return null;
        }
        return $maxConcurrentLogins;
    }
}
