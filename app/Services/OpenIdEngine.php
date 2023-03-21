<?php

namespace App\Services;

use App\Http\APIError;
use App\Mail\Mailer;
use App\Models\AnalyticsEvent;
use App\Models\AppProfile;
use App\Models\OpenIdToken;
use App\Models\User;
use App\Responses\LoginResponse;
use App\Services\Users\TokenLoginEngine;
use App\Services\Users\TokenLoginException;
use Cache;
use Carbon\Carbon;
use DB;
use Exception;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Http;

class OpenIdEngine
{
    // Cache Lifetime in Seconds
    const CACHE_LIFETIME = 60 * 60;
    const NONCE_LIFETIME = 60 * 60;
    const OPENID_DISCOVERY_PATH = '/.well-known/openid-configuration';
    // dummy value to set the password field to,
    // will prevent logging in via username+password
    // see: https://security.stackexchange.com/a/251642
    const OPENID_PASSWORD_DUMMY = '**OPENID-ONLY**';

    const NAME_FIELDS = [
        'preferred_username',
        'nickname',
        'name',
        'given_name',
    ];

    private AppProfile $appProfile;
    private TokenLoginEngine $tokenLoginEngine;

    function __construct(AppProfile $appProfile)
    {
        $this->appProfile = $appProfile;
        if (!$this->appProfile->getValue('openid_enabled')) {
            throw new Exception('App Profile does not have OpenID enabled!');
        }
        $this->tokenLoginEngine = new TokenLoginEngine($appProfile);
    }

    /**
     * Returns the URL the client's browser needs to open
     * to do the Open ID authentication.
     *
     * @param string|null $appId ID of native app, if given
     * @return string
     */
    public function getAuthUrl(?string $appId = null): string
    {
        $metadata = $this->getMeta();
        $authUrl = $metadata['authorization_endpoint'];
        if($this->appProfile->getValue('openid_claims')){
            $claims = $this->appProfile->getValue('openid_claims');
        }else{
            $claims = 'openid profile email';
        }
        $nonce = $this->tokenLoginEngine->createRandomToken();
        Cache::put('openid-nonce:' . $nonce, true, self::NONCE_LIFETIME);
        $authUrlData = [
            'client_id' => $this->appProfile->getValue('openid_client_id'),
            'response_type' => 'id_token',
            'redirect_uri' => route('openid.receiveToken'),
            'response_mode' => 'form_post',
            'scope' => $claims,
            'nonce' => $nonce,
            'state' => base64_encode(http_build_query([
                'profile_id' => $this->appProfile->id,
                'native_app_id' => $appId,
            ])),
        ];
        return $authUrl . '?' . http_build_query($authUrlData);
    }

    public function getMeta()
    {
        $authorityUrl = $this->appProfile->getValue('openid_authority_url');
        if (!$authorityUrl) {
            throw new Exception('App Profile does not have an OpenID Authority Url set!');
        }
        $authorityUrl .= self::OPENID_DISCOVERY_PATH . '?appid=' . $this->appProfile->getValue('openid_client_id');
        return Cache::remember(
            'openid-meta-' . $authorityUrl,
            self::CACHE_LIFETIME,
            function () use ($authorityUrl) {
                $response = Http::get($authorityUrl)->json();
                if (!$response) {
                    throw new Exception('Could not fetch OpenID metadata!');
                }
                return $response;
            });
    }

    public function getJwtKeys()
    {
        $keysUrl = $this->getMeta()['jwks_uri'];
        return Cache::remember(
            'openid-keys-' . $keysUrl,
            self::CACHE_LIFETIME,
            function () use ($keysUrl) {
                $response = Http::get($keysUrl)->json();
                if (!$response) {
                    throw new Exception('Could not fetch JWT keys!');
                }
                return $response;
            });
    }

    public function decodeJwt($jwtString)
    {
        $keys = $this->getJwtKeys();
        $meta = $this->getMeta();
        $jwt = JWT::decode($jwtString, JWK::parseKeySet($keys, $meta['id_token_signing_alg_values_supported'][0]));

        $dateFields = ['iat', 'nbf', 'exp'];
        collect($dateFields)->each(function($dateField) {
            if(isset($jwt->{$dateField}) && !is_string($jwt->{$dateField})) {
                throw new Exception('Invalid input format for ' . $dateField);
            }
        });

        if (Carbon::parse($jwt->iat)->isFuture()) {
            throw new Exception('Implausible JWT iat!');
        }
        if (isset($jwt->nbf) && Carbon::parse($jwt->nbf)->isFuture()) {
            throw new Exception('JWT not yet valid!');
        }
        if (isset($jwt->exp) && Carbon::parse($jwt->exp)->isPast()) {
            throw new Exception('JWT expired!');
        }
        if ($jwt->aud != $this->appProfile->getValue('openid_client_id')) {
            throw new Exception('Token has wrong audience!');
        }
        if (!Cache::get('openid-nonce:' . $jwt->nonce)) {
            throw new Exception('JWT nonce failed validation!');
        }
        Cache::forget('openid-nonce:' . $jwt->nonce);

        return $jwt;
    }

    public function getUserFromToken($token)
    {
        $identifier = $this->getIdentifierFromToken($token);
        // user already logged in with this token
        $user = User::whereIn('id', function ($query) use ($identifier) {
            $query->select('user_id')
                ->from('open_id_tokens')
                ->where('token', $identifier);
        })->where('app_id', $this->appProfile->app_id)->first();

        if (!$user) {
            // first time openid login, but user exists
            $user = User::where('email', $token->email)
                ->where('app_id', $this->appProfile->app_id)
                ->first();
            if ($user) {
                $openIdToken = new OpenIdToken();
                $openIdToken->user_id = $user->id;
                $openIdToken->token = $identifier;
                $openIdToken->save();
            }
        }

        return $user;
    }

    public function createUserFromToken($token)
    {
        return DB::transaction(function () use ($token) {
            if (!$this->appProfile->getValue('enable_sso_registration')) {
                return null;
            }
            // signup is server-side-disabled on the Demo
            // so it still shows the signup forms in the app
            if ($this->appProfile->app->demoSignupDisabled()) {
                return null;
            }

            $name = '';
            foreach (self::NAME_FIELDS as $nameField) {
                if (isset($token->{$nameField}) && strlen($token->{$nameField})) {
                    $name = $token->{$nameField};
                    break;
                }
            }
            // if we ended up with an email, take only the local-part
            $name = utrim(explode('@', $name)[0]);

            if (!$name) {
                throw new Exception('No valid username received!');
            }

            $user = new User();
            $user->password = self::OPENID_PASSWORD_DUMMY;
            $user->username = $name;
            $user->email = $token->email;
            $user->app_id = $this->appProfile->app_id;
            $user->language = language($this->appProfile->app_id);
            $user->active = true;
            if(isset($token->given_name)) {
                $user->firstname = utrim($token->given_name);
            }
            if(isset($token->family_name)) {
                $user->lastname = utrim($token->family_name);
            }
            $user->save();

            // Attach the tags of the app profile to the user
            $user->tags()->attach($this->appProfile->tags);

            $openIdToken = new OpenIdToken();
            $openIdToken->user_id = $user->id;
            $openIdToken->token = $this->getIdentifierFromToken($token);
            $openIdToken->save();

            $mailer = new Mailer();
            $mailer->sendSSOWelcomeMail($user);

            AnalyticsEvent::log($user, AnalyticsEvent::TYPE_USER_CREATED);

            return $user;
        });
    }

    public function doTokenLogin(string $loginToken)
    {
        try {
            $user = $this->tokenLoginEngine->getUserFromToken($loginToken);
        } catch (TokenLoginException $e) {
            return new APIError($e->getMessage(), 401);
        }

        if ($user->app_id !== $this->appProfile->app_id) {
            return new APIError(__('errors.invalid_login_data'));
        }
        if ($user->loginSuspended()) {
            return new APIError(__('errors.account_login_disabled'), 401);
        }
        if (!$user->openIdTokens()->count()) {
            return new APIError(__('errors.invalid_login_data'), 401);
        }
        try {
            if (!$token = auth('api')->login($user)) {
                return new APIError(__('errors.invalid_login_data'), 401);
            }
        } catch (Exception $e) {
            return new APIError(__('errors.could_not_create_token'), 401);
        }
        $user = auth('api')->setToken($token)->user();

        $appSettings = app(AppSettings::class);
        if ($appSettings->getValue('save_user_ip_info')) {
            $user->country = IPGeolocation::getInstance()->isoCode(request()->ip());
        }
        $user->failed_login_attempts = 0;
        if ($user->force_password_reset) {
            $user->password = self::OPENID_PASSWORD_DUMMY;
            $user->force_password_reset = false;
        }
        $user->save();

        $deletedTokens = (new AuthEngine)->updateAuthTokens($user, $token);

        return new LoginResponse($user, $token, $deletedTokens);
    }

    public function getIdentifierFromToken($token)
    {
        return isset($token->sub) ? $token->sub : null;
    }
}
