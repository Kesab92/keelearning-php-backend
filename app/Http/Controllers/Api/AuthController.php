<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\Users\UserCreationException;
use App\Http\APIError;
use App\Http\Controllers\Controller;
use App\Mail\Mailer;
use App\Models\AnalyticsEvent;
use App\Models\App;
use App\Models\AppProfile;
use App\Models\AppProfileSetting;
use App\Models\FcmToken;
use App\Models\Tag;
use App\Models\TagGroup;
use App\Models\User;
use App\Models\VoucherCode;
use App\Responses\LoginResponse;
use App\Services\AppSettings;
use App\Services\AuthEngine;
use App\Services\IPGeolocation;
use App\Services\VoucherEngine;
use Config;
use Exception;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request as Input;
use Illuminate\Validation\Rule;
use Response;
use Throwable;
use Validator;

class AuthController extends Controller
{
    private $mailer;

    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Fetches all tag groups & tags for the current app that are selectable.
     *
     * @param $app_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSelectableTagGroups($app_id)
    {
        $app = App::findOrFail($app_id);
        $tagGroups = TagGroup::where('app_id', $app->id)
            ->where('signup_selectable', true)
            ->with(['tags' => function ($query) {
                $query->orderBy('label');
            }])
            ->orderBy('name')
            ->get();

        return Response::json($tagGroups->toArray());
    }

    /**
     * Fetches all necessary data for signup (tag groups, metadata).
     *
     * @param $app_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSignupData($app_id, $profile_id = null)
    {
        $app = App::findOrFail($app_id);
        $tagGroups = TagGroup::where('app_id', $app->id)
            ->where('signup_selectable', true)
            ->with(['tags' => function ($query) {
                $query->orderBy('label');
            }])
            ->orderBy('name')
            ->get()
            ->toArray();

        return Response::json([
            'meta_fields' => $app->getSignupMetaFields(),
            'tag_groups' => $tagGroups,
        ]);
    }

    /**
     * The function attempts a login via the received credentials or the saved token.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws Exception
     */
    public function postLogin(Request $request)
    {
        $credentials = [
            'email' => $request->get('email'),
            'password' => $request->get('password'),
            'app_id' => $request->input('appId'),
        ];

        $app = App::findOrFail($credentials['app_id']);
        $appProfile = $this->getAppProfile($app, $request->input('profileId'));

        $mailUser = null;

        // 1. check login meta field if available
        $loginMetaField = $app->getLoginMetaField();
        if ($loginMetaField) {
            $mailUser = User::getByMetafield($app->id, $loginMetaField, $credentials['email'])
                ->first();
        }

        // 2. check email
        if (!$mailUser) {
            $mailUser = User::where('app_id', $app->id)
                ->where('email', $credentials['email'])
                ->first();
        }

        // 3. check username if allowed
        if (!$mailUser && $app->allowMaillessSignup()) {
            $mailUser = User::ofApp($app->id)->where('username', $credentials['email'])
                ->first();
        }

        if ($mailUser) {
            // fill credentials with found user's mail,
            // in case we logged in via something different
            $credentials['email'] = $mailUser->getRawOriginal('email');

            // Check if the user has access to the selected app profile
            if ($appProfile->id !== $mailUser->getAppProfile()->id) {
                return new APIError(__('errors.invalid_login_data'), 401);
            }

            if ($mailUser->is_bot) {
                return new APIError(__('errors.invalid_login_data'), 401);
            }

            if ($mailUser->loginSuspended()) {
                if ($request->get('acceptJsonError')) {
                    return Response::json([
                        'success' => false,
                        'error' => 'account_login_disabled',
                    ]);
                } else {
                    return new APIError(__('errors.account_login_disabled'), 401);
                }
            }
        }

        try {
            // attempt to verify the credentials and create a token for the user
            if (!$token = auth('api')->attempt($credentials)) {
                User::failedLoginAttempt($credentials['email'], $app->id);

                return new APIError(__('errors.invalid_login_data'), 401);
            }
        } catch (Exception $e) {
            // something went wrong whilst attempting to encode the token
            return new APIError(__('errors.could_not_create_token'), 401);
        }

        // first check if the user is active. if all is good return the token, id and username
        /** @var User $user */
        $user = auth('api')->setToken($token)->user();

        if (!is_null($user->deleted_at)) {
            return new APIError(__('errors.user_deleted'));
        }

        if ($user->active == 0 || $user->is_dummy || $user->is_api_user) {
            return new APIError(__('errors.user_inactive'));
        }

        // If the user is not of the current application
        if ($user->app_id != $request->input('appId')) {
            return new APIError(__('errors.user_wrong_app', ['appname' => $user->app->name], $user->app->getLanguage()));
        }

        $appSettings = app(AppSettings::class);
        if ($appSettings->getValue('save_user_ip_info')) {
            $user->country = IPGeolocation::getInstance()->isoCode($request->ip());
        }
        // if the user didn't accept the ToS yet,
        // update his language to what was detected
        // during the login process
        if (!$user->tos_accepted && in_array(request()->header('X-LANGUAGE'), appLanguages())) {
            $user->language = request()->header('X-LANGUAGE');
        }
        $user->failed_login_attempts = 0;
        $user->save();

        $deletedTokens = (new AuthEngine)->updateAuthTokens($user, $token);

        return new LoginResponse($user, $token, $deletedTokens);
    }

    private function getAppProfile(App $app, $profileId = null)
    {
        if ($profileId) {
            $profile = AppProfile
                ::where('app_id', $app->id)
                ->where('id', $profileId)
                ->first();
            if ($profile) {
                return $profile;
            }
        }
        return $app->getDefaultAppProfile();
    }

    public function acceptToS()
    {
        $user = user();
        $user->tos_accepted = 1;
        $user->save();

        $response = [
            'force_password_reset' => $user->force_password_reset,
            'tos_accepted' => $user->tos_accepted,
            'success' => true,
        ];

        return Response::json($response);
    }

    public function getContact($app_id, $profileId = null)
    {
        $app = App::findOrFail($app_id);

        $profile = null;
        if ($profileId) {
            $profile = AppProfile::where('app_id', $app_id)->where('id', $profileId)->first();
        }
        if (!$profile) {
            $profile = $app->getDefaultAppProfile();
        }

        return Response::json([
            'mail' => $profile->getValue('contact_email'),
        ]);
    }

    /**
     * Reset the user password and send an email.
     *
     * @param Request $request
     *
     * @return APIError|\Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        // Check if the email is valid
        if ($validator->fails()) {
            return new APIError(__('errors.invalid_mail', []));
        }

        $email = $request->get('email');
        $user = User::whereEmail($email)
            ->where('app_id', $request->get('appId'))
            ->first();

        // Check if there is a user with this email
        if ($user == null) {
            return new APIError(__('errors.no_user_with_mail', []));
        }

        if (!is_null($user->deleted_at)) {
            return new APIError(__('errors.user_deleted', []));
        }

        // Check if the user is active
        if ($user->active == 0) {
            return new APIError(__('errors.user_inactive', []));
        }

        // Get a new password, save it and send it via email
        $password = randomPassword();
        $this->mailer->sendResetEmail($user, $password);

        $user->password = Hash::make($password);

        if ($user->getAppProfile()->getValue('signup_force_password_reset', false, true)) {
            $user->force_password_reset = true;
        }

        $user->save();

        return Response::json([
            'email' => $email,
        ]);
    }

    public function setFCMId()
    {
        $user = user();
        if (Input::get('type') == 'Android') {
            $user->fcm_id = Input::get('device_registration_id');
        }
        if (Input::get('type') == 'iOS') {
            $user->apns_id = Input::get('device_registration_id');
        }
        if (Input::get('type') == 'Browser') {
            $user->gcm_id_browser = Input::get('device_registration_id');
        }
        $user->save();
    }

    public function setGCMAuth()
    {
        $user = user();
        $user->gcm_browser_p256dh = Input::get('gcm_p256dh');
        $user->gcm_browser_auth = Input::get('gcm_auth');
        $user->save();
    }

    /**
     * Creates a new user account.
     *
     * @param Request $request
     * @return APIError|\Illuminate\Http\JsonResponse
     * @throws Throwable
     */
    public function postSignup(Request $request)
    {
        $this->validate($request, [
            'appId' => [
                'required',
                'integer',
                Rule::exists('apps', 'id'),
            ],
            'profileId' => [
                'integer',
                Rule::exists('app_profiles', 'id')->where(function ($query) use ($request) {
                    return $query->where('app_id', $request->input('appId'));
                }),
            ],
        ]);

        /** @var App $app */
        $app = App::findOrFail($request->input('appId'));
        $appProfile = $this->getAppProfile($app, $request->input('profileId'));

        $validationRules = [
            'username' => 'required|min:2|max:255',
        ];

        $profileNeedsMail = $appProfile->getValue('signup_show_email') && $appProfile->getValue('signup_show_email_mandatory') === 'mandatory';
        $needsMail = !$app->allowMaillessSignup() || $profileNeedsMail || $request->input('email');

        if ($needsMail) {
            $validationRules['email'] = 'required|email|min:3|max:255';
        }

        $profileNeedsFirstname = $appProfile->getValue('signup_show_firstname') && $appProfile->getValue('signup_show_firstname_mandatory') === 'mandatory';
        $profileNeedsLastname = $appProfile->getValue('signup_show_lastname') && $appProfile->getValue('signup_show_lastname_mandatory') === 'mandatory';

        if ($app->needsNameForSignup() || $profileNeedsFirstname) {
            $validationRules['firstname'] = 'required';
        }
        if ($app->needsNameForSignup() || $profileNeedsLastname) {
            $validationRules['lastname'] = 'required';
        }


        $loginMetaField = $app->getLoginMetaField();
        foreach ($app->getSignupMetaFields() as $key => $metaField) {
            switch ($metaField['type']) {
                case 'date':
                    $validationRules['meta.' . $key] = ['date_format:Y-m-d'];
                    break;
                default:
                    $validationRules['meta.' . $key] = [];
                    break;
            }
            if (!isset($metaField['signup_optional']) || !$metaField['signup_optional']) {
                $validationRules['meta.' . $key][] = 'required';
            }
            if ($key == $loginMetaField) {
                if (!$needsMail) {
                    $validationRules['meta.' . $key][] = 'required';
                }
                $validationRules['meta.' . $key][] = 'min:2';
                $validationRules['meta.' . $key][] = 'max:255';
            }
        }

        $this->validate($request, $validationRules);

        return DB::transaction(function () use ($profileNeedsMail, $needsMail, $appProfile, $app, $request) {
            $lockPrefix = 'storeUser-' . $app->id . '-';
            $lockByUsername = null;
            $lockByEmail = null;
            $user = null;

            if ($app->uniqueUsernames()) {
                $lockByUsername = Cache::lock($lockPrefix . $request->input('username'), 5);
                if (!$lockByUsername->get()) {
                    abort(403);
                }
            }
            if ($needsMail) {
                $lockByEmail = Cache::lock($lockPrefix . $request->input('email'), 5);
                if (!$lockByEmail->get()) {
                    abort(403);
                }
            }


            try {
                $user = $this->createUser($app, $appProfile, $request, $needsMail, $profileNeedsMail);
            } catch (UserCreationException $e) {
                return $e->getErrorResponse();
            } finally {
                optional($lockByUsername)->release();
                optional($lockByEmail)->release();
            }

            $credentials = [
                'app_id' => $app->id,
                'email' => $user->getRawOriginal('email'),
                'password' => Input::get('password'),
            ];

            // Attempt to verify the credentials and create a token for the user
            if (!$token = auth('api')->attempt($credentials)) {
                return new APIError(__('errors.user_creation_failed'), 401);
            }

            $appSettings = app(AppSettings::class);
            if ($appSettings->getValue('save_user_ip_info')) {
                $user->country = IPGeolocation::getInstance()->isoCode($request->ip());
                $user->save();
            }

            if (!$user->isMaillessAccount()) {
                $this->mailer->sendWelcomeMail($user);
            }

            (new AuthEngine)->updateAuthTokens($user, $token);

            AnalyticsEvent::log($user, AnalyticsEvent::TYPE_USER_CREATED);

            return new LoginResponse($user, $token);
        });
    }

    /**
     * Create a temporary user account.
     *
     * @return APIError|LoginResponse
     * @throws Exception
     */
    public function tmpAccount(Request $request)
    {
        $this->validate($request, [
            'appId' => [
                'required',
                'integer',
                Rule::exists('apps', 'id'),
            ],
            'profileId' => [
                'integer',
                Rule::exists('app_profiles', 'id')->where(function ($query) use ($request) {
                    return $query->where('app_id', $request->input('appId'));
                }),
            ],
        ]);

        $username = utrim($request->get('username'));
        if (!$username) {
            return new APIError(__('errors.check_input'), 403);
        }

        $app = App::find($request->input('appId'));
        $appProfile = $this->getAppProfile($app, $request->input('profileId'));

        if (!$app->hasTmpAccounts() && !$appProfile->getValue('signup_has_temporary_accounts')) {
            return new APIError(__('errors.temporary_accounts_disabled'), 403);
        }

        if($app->demoSignupDisabled()) {
            return new APIError(__('errors.signup_disabled_in_demo'), 403);
        }

        if ($app->uniqueUsernames() && User::where('username', $username)->where('app_id', Input::get('appId'))->count()) {
            return new APIError(__('errors.username_taken'), 400);
        }

        $credentials = [
            'email' => 'tmp' . uniqid() . '@sopamo.de',
            'password' => randomPassword(24),
        ];

        $user = new User();
        $user->password = Hash::make($credentials['password']);
        $user->active = 1;
        $user->username = $username;
        $user->email = $credentials['email'];
        $user->app_id = $request->get('appId');
        $user->language = language($request->get('appId'));
        $user->save();

        $newUserTags = [];

        // Attach the tags of the app profile to the user
        foreach ($appProfile->tags as $tag) {
            $newUserTags[] = $tag->id;
        }

        // App specific assignment
        switch ($user->app_id) {
            case App::ID_DECISIO:
                $newUserTags[] = App::DATA_DECISIO['guest_tag_id'];
                break;
            case App::ID_ALLISON:
                $newUserTags[] = App::DATA_ALLISON['guest_tag_id'];
                break;
            case App::ID_WICHTEL_WISSEN:
                $newUserTags[] = App::DATA_WICHTEL_WISSEN['guest_tag_id'];
                break;
        }
        if ($newUserTags) {
            $user->tags()->sync($newUserTags);
        }

        // attempt to verify the credentials and create a token for the user
        if (!$token = auth('api')->attempt($credentials)) {
            return new APIError(__('errors.user_creation_failed'), 401);
        }

        $appSettings = app(AppSettings::class);
        if ($appSettings->getValue('save_user_ip_info')) {
            $user->country = IPGeolocation::getInstance()->isoCode($request->ip());
            $user->save();
        }

        (new AuthEngine)->updateAuthTokens($user, $token);

        AnalyticsEvent::log($user, AnalyticsEvent::TYPE_USER_CREATED);

        return new LoginResponse($user, $token);
    }

    public function convertAccount(Request $request)
    {
        $username = utrim($request->get('username'));
        if (!$username) {
            return new APIError(__('errors.check_input'), 403);
        }

        if (
            user()->app->uniqueUsernames()
            && User::where('username', $username)
                ->where('id', '!=', user()->id)
                ->where('app_id', user()->app_id)
                ->exists()
        ) {
            return new APIError(__('errors.username_taken'), 400);
        }

        if (!user()->app->hasTmpAccounts() && !user()->getAppProfile()->getValue('signup_has_temporary_accounts')) {
            return new APIError(__('errors.temporary_accounts_disabled'), 403);
        }

        if (!user()->isTmpAccount()) {
            return new APIError(__('errors.account_already_converted'), 401);
        }

        $credentials = [
            'email' => utrim($request->get('email')),
            'password' => $request->get('password'),
        ];

        $this->validate($request, [
            'email' => 'required|email|min:3|max:255',
            'password' => 'required|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).+$/',
        ]);

        // Check if a user with this email already exists
        $user = User::where('email', $credentials['email'])->where('app_id', user()->app_id)->count();
        if ($user) {
            return new APIError(__('errors.mail_taken'), 401);
        }

        $user = user();
        $user->password = Hash::make($credentials['password']);
        $user->username = $username;
        $user->email = utrim($credentials['email']);
        $user->firstname = $request->input('firstname');
        $user->lastname = $request->input('lastname');
        $user->save();

        // attempt to verify the credentials and create a token for the user
        if (!$token = auth('api')->attempt($credentials)) {
            return new APIError(__('errors.login_again'), 401);
        }

        (new AuthEngine)->updateAuthTokens($user, $token);

        return new LoginResponse($user, $token);
    }

    public function setPassword()
    {
        $user = user();
        if ($user->app_id !== App::ID_FORD) {
            return new APIError(__('errors.password_change_admin_only'));
        }
        $newPassword = Input::get('password');
        $validator = Validator::make([
            'password' => $newPassword,
        ], [
            'password' => 'required|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).+$/',
        ]);
        if ($validator->fails()) {
            return Response::json(['success' => false]);
        }
        $user->password = Hash::make($newPassword);

        return Response::json(['success' => $user->save()]);
    }

    /**
     * Sets user supplied password.
     */
    public function setInsecurePassword(Request $request)
    {
        $newpw = $request->get('newpw');
        $user = user();
        if (!$user->app->hasInsecurePasswordChange()) {
            return new APIError(__('errors.password_change_admin_only'));
        }

        if (!Hash::check($request->get('oldpw'), $user->password)) {
            return new APIError(__('errors.password_wrong'));
        }

        $badwords = [
            $user->username,
            $user->firstname,
            $user->lastname,
            $user->app->name,
        ];
        $passwordValidationResult = validatePassword($newpw, $badwords);
        if ($passwordValidationResult['valid'] === false) {
            $errormsg = __('errors.password_too_weak') . ":\n";
            foreach ($passwordValidationResult['result']->getErrors() as $error) {
                $errormsg .= '• ' . $error . "\n";
            }

            return new APIError($errormsg);
        }
        if (strlen($newpw) < 8) {
            return new APIError(__('errors.password_min_length', ['minlength' => 8]));
        }
        if ($user->isTmpAccount()) {
            return new APIError(__('errors.account_needs_conversion'));
        }
        $user->password = Hash::make($newpw);

        return Response::json(['success' => $user->save()]);
    }

    /**
     * Sets a new password and username.
     */
    public function forcePasswordReset(Request $request)
    {
        $newPassword = $request->input('newPassword');
        $newUsername = utrim($request->input('newUsername'));

        $user = user();
        if (!$user->force_password_reset) {
            return new APIError(__('errors.no_permission'));
        }

        $app = $user->app;
        $appProfile = $user->getAppProfile();

        $badwords = [
            $user->username,
            $user->firstname,
            $user->lastname,
            $user->app->name,
        ];
        $passwordValidationResult = validatePassword($newPassword, $badwords);
        if ($passwordValidationResult['valid'] === false) {
            $errormsg = __('errors.password_too_weak') . ":";
            foreach ($passwordValidationResult['result']->getErrors() as $error) {
                $errormsg .= ' • ' . $error;
            }
            return new APIError($errormsg);
        }

        if (Hash::check($newPassword, $user->password)) {
            return new APIError(__('errors.new_password_identical_to_old'));
        }

        if (strlen($newPassword) < 8) {
            return new APIError(__('errors.password_min_length', ['minlength' => 8]));
        }

        $user->password = Hash::make($newPassword);

        if ($appProfile->getValue('allow_username_change') && $newUsername) {
            if (strlen($newUsername) < 3) {
                return new APIError(__('errors.username_min_chars', ['minlength' => 3]));
            }
            if ($app->uniqueUsernames() && $newUsername !== $user->username && User::where('username', $newUsername)->where('app_id', $app->id)->count()) {
                return new APIError(__('errors.username_taken'), 400);
            }
            $user->username = $newUsername;
        }
        $user->force_password_reset = false;

        return Response::json(['success' => $user->save()]);
    }


    /**
     * Adds mail address to mailless account.
     */
    public function addMail(Request $request)
    {
        $email = utrim($request->get('email'));
        $user = user();
        if (!$user->isMaillessAccount()) {
            return new APIError('You can\'t do that.');
        }

        $this->validate($request, [
            'email' => 'required|email|min:3|max:255',
        ]);

        if (User::where('email', $email)->where('app_id', $user->app_id)->count()) {
            return new APIError(__('errors.mail_taken'), 400);
        }

        $mailValid = $user->app->isMailValid($email);
        if ($mailValid !== true) {
            return new APIError($mailValid, 400);
        }

        $user->email = $email;
        $user->save();

        return Response::json(['success' => true]);
    }

    /**
     * Serves as an endpoint for deepstream to check if the login data is valid.
     *
     * @param Request $request
     *
     * @return APIError|\Illuminate\Http\JsonResponse
     */
    public function deepstreamlogin(Request $request)
    {
        $authData = $request->get('authData');

        if ($authData['username'] == '0') {
            if ($authData['password'] == Config::get('services.deepstream.token')) {
                return response()->json([
                    'username' => 'admin',
                    'userId' => 0,
                    'clientData' => [],
                    'serverData' => ['admin' => true],
                ]);
            } else {
                return new APIError('Invalid admin auth token', 403);
            }
        }

        $user = auth('api')->setToken($authData['password'])->user();

        if (!$user) {
            return new APIError(__('errors.invalid_token', []), 403);
        }

        if ($user->id != $authData['username']) {
            return new APIError(__('errors.invalid_token', []), 403);
        }

        if (!is_null($user->deleted_at)) {
            return new APIError(__('errors.user_deleted', []), 403);
        }

        return response()->json([
            'username' => $user->id,
            'userId' => $user->id,
            'clientData' => [],
            'serverData' => [],
        ]);
    }

    public function checkSlug(Request $request)
    {
        $appProfileSetting = AppProfileSetting
            ::where('key', 'slug')
            ->where('value', $request->input('slug'))
            ->first();
        if (!$appProfileSetting) {
            return Response::json([], 404);
        }
        $appProfile = $appProfileSetting->appProfile;
        if (!$appProfile) {
            return Response::json([], 404);
        }
        $app = $appProfile->app;
        if (!$app) {
            return Response::json([], 404);
        }
        return Response::json([
            'id' => $app->id,
            'name' => $app->app_name,
        ]);
    }

    public function logout(Request $request)
    {
        user()->authTokens()->where('token', auth('api')->getToken())->delete();

        $fcmToken = $request->input('token');
        if ($fcmToken) {
            FcmToken
                ::where('user_id', user()->id)
                ->where('token', $fcmToken)
                ->delete();
        }

        return Response::json([]);
    }

    /**
     * @param App $app
     * @param AppProfile $appProfile
     * @param Request $request
     * @param bool $needsMail
     * @param bool $profileNeedsMail
     * @return User
     * @throws UserCreationException
     * @throws Throwable
     */
    private function createUser(App $app, AppProfile $appProfile, Request $request, bool $needsMail, bool $profileNeedsMail):User {
        if (!$app->hasSignup() && !$appProfile->getValue('signup_enabled')) {
            throw new UserCreationException(new APIError(__('errors.no_signup'), 403));
        }

        if ($app->demoSignupDisabled()) {
            throw new UserCreationException(new APIError(__('errors.signup_disabled_in_demo'), 403));
        }

        $email = utrim($request->get('email'));
        $username = utrim($request->get('username'));

        if (!$username) {
            throw new UserCreationException(new APIError(__('errors.check_input')));
        }

        $badwords = [
            $username,
            $app->name,
        ];

        $meta = [];
        $inputMeta = $request->get('meta');
        $loginMetaField = $app->getLoginMetaField();
        foreach ($app->getSignupMetaFields() as $key => $metaField) {
            if (isset($inputMeta[$key])) {
                $meta[$key] = $inputMeta[$key];
                $badwords[] = $inputMeta[$key];
            } else {
                $meta[$key] = '';
            }
        }

        $passwordValidationResult = validatePassword($request->get('password', ''), $badwords);
        if ($passwordValidationResult['valid'] === false) {
            $errormsg = __('errors.password_too_weak') . ":<br \>";
            foreach ($passwordValidationResult['result']->getErrors() as $error) {
                $errormsg .= '• ' . $error . '<br />';
            }
            throw new UserCreationException(new APIError($errormsg));
        }

        if ($app->id === App::ID_SIGNIA_PRO) {
            $path = base_path() . '/storage/dictionaries/Kundenliste-Signia.php';
            if (!file_exists($path)) {
                throw new UserCreationException(new APIError(__('errors.user_creation_failed'), 401));
            }
            $data = require $path;

            // Check vendor number
            $elements = array_filter($data, function ($item) use ($meta) {
                return $item[0] === $meta['vendor'];
            });

            if (count($elements) === 0) {
                throw new UserCreationException(new APIError(__('errors.app_code_failed'), 400));
            }
        }

        if (($needsMail) &&
            User::where('email', $email)->where('app_id', $app->id)->count()
        ) {
            throw new UserCreationException(new APIError(__('errors.mail_taken'), 400));
        }

        if ($app->uniqueUsernames() && User::where('username', $username)->where('app_id', $app->id)->count()) {
            throw new UserCreationException(new APIError(__('errors.username_taken'), 400));
        }

        $uniqueMetaFields = $app->getUniqueMetaFields();
        foreach ($uniqueMetaFields as $uniqueMetaField) {
            if (isset($meta[$uniqueMetaField])) {
                $existingUsers = User::getByMetafield($app->id, $uniqueMetaField, $meta[$uniqueMetaField]);
                if ($existingUsers->count()) {
                    throw new UserCreationException(new APIError(__('errors.duplicate_meta_field_data', ['metafield' => $app->getAllUserMetaDataFields()[$uniqueMetaField]['label']]), 400));
                }
            }
        }

        if ($needsMail) {
            $mailValid = $app->isMailValid($email);
            if ($mailValid !== true) {
                throw new UserCreationException(new APIError($mailValid, 400));
            }
        }

        // Validate the voucher if a code has been given
        $voucherCode = null;

        $voucherEngine = app(VoucherEngine::class);

        if ($request->input('voucher')) {
            $voucherCode = VoucherCode::where('code', $request->input('voucher'))
                ->whereNull('user_id')
                ->whereNull('cash_in_date')
                ->whereHas('voucher', function ($query) use ($app) {
                    $query->where('app_id', $app->id);
                })->first();
            if (!$voucherCode) {
                $voucherCodeExists = VoucherCode::where('code', $request->input('voucher'))
                    ->whereHas('voucher', function ($query) use ($app) {
                        $query->where('app_id', $app->id);
                    })->count();
                if ($voucherCodeExists) {
                    throw new UserCreationException(new APIError(__('errors.voucher_in_use')));
                }

                throw new UserCreationException(new APIError(__('errors.voucher_not_found')));
            }
            $validationResult = $voucherEngine->validateCode($voucherCode, $app->id);
            if ($validationResult instanceof APIError) {
                return $validationResult;
            }
        }
        $profileNeedsVoucher = $appProfile->getValue('signup_show_voucher') && $appProfile->getValue('signup_show_voucher_mandatory') === 'mandatory';
        if (!$voucherCode && ($app->needsVoucherForSignup($email) || $profileNeedsVoucher)) {
            throw new UserCreationException(new APIError(__('errors.voucher_needed'), 400));
        }

        // go through available signup tag groups and set selection
        $availableTagGroups = TagGroup::where('app_id', $app->id)
            ->where('signup_selectable', true)
            ->get();
        $selectedTags = $request->get('tags');
        $newUserTags = [];
        foreach ($availableTagGroups as $tg) {
            if (!isset($selectedTags[$tg->id])) {
                // some tag groups are required for signup
                if (!$tg->signup_required) {
                    continue;
                }

                throw new UserCreationException(new APIError(__('errors.missing_tag'), 400));
            }
            $tag = Tag::find($selectedTags[$tg->id]);
            if (!$tag || $tag->app_id != $app->id || $tag->tag_group_id != $tg->id) {
                throw new UserCreationException(new APIError(__('errors.invalid_tag'), 400));
            }
            $newUserTags[] = $selectedTags[$tg->id];
        }

        // Attach the tags of the app profile to the user
        foreach ($appProfile->tags as $tag) {
            $newUserTags[] = $tag->id;
        }

        if (
            !$email
            && $app->allowMaillessSignup()
            && !$profileNeedsMail
        ) {
            $email = createDummyMail();
        }

        $credentials = [
            'email' => $email,
            'password' => $request->get('password'),
        ];

        $user = new User();
        $user->password = Hash::make($credentials['password']);
        $user->active = !$app->needsAccountActivation();
        $user->username = $username;
        $user->firstname = $request->get('firstname') ?? '';
        $user->lastname = $request->get('lastname') ?? '';
        $user->email = $credentials['email'];
        $user->app_id = $request->get('appId');
        $user->language = language(Input::get('appId'));
        $user->save();
        $user->tags()->attach($newUserTags);
        $user->setMeta($meta);

        // Redeems the voucher if a valid code has been given
        if ($voucherCode) {
            $voucherEngine->redeemCode($voucherCode, $user);
        }

        return $user;
    }
}
