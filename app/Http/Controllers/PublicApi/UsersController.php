<?php

namespace App\Http\Controllers\PublicApi;

use App\Http\APIError;
use App\Http\Controllers\Controller;
use App\Http\Requests\PublicApi\User\UserDeleteFormRequest;
use App\Http\Requests\PublicApi\User\UserListFormRequest;
use App\Http\Requests\PublicApi\User\UserStoreFormRequest;
use App\Http\Requests\PublicApi\User\UserUpdateFormRequest;
use App\Mail\Mailer;
use App\Models\AccessLog;
use App\Models\AnalyticsEvent;
use App\Models\User;
use App\Services\AccessLogEngine;
use App\Services\AccessLogMeta\AccessLogUserAdd;
use App\Services\AccessLogMeta\AccessLogUserUpdate;
use App\Transformers\PublicApi\UserTransformer;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    /**
     * Returns a list of users.
     *
     * @param UserListFormRequest $request
     * @param UserTransformer $userTransformer
     * @return JsonResponse
     */
    public function index(UserListFormRequest $request, UserTransformer $userTransformer)
    {
        $appId = Auth::user()->app_id;

        $validated = $request->validated();

        $users = User::ofApp($appId)
            ->where('is_dummy', false)
            ->where('is_api_user', false)
            ->offset($validated['perPage'] * $validated['page'])
            ->limit($validated['perPage'])
            ->with(['metafields', 'tags'])
            ->get();

        return response()->json($userTransformer->transformAll($users));
    }

    /**
     * Stores the user.
     *
     * @param UserStoreFormRequest $request
     * @param UserTransformer $userTransformer
     * @param AccessLogEngine $accessLogEngine
     * @param Mailer $mailer
     * @return JsonResponse
     */
    public function store(UserStoreFormRequest $request, UserTransformer $userTransformer, AccessLogEngine $accessLogEngine, Mailer $mailer)
    {
        $validated = $request->validated();

        $apiUser = Auth::user();
        $app = $apiUser->app;
        $appProfile = $app->getDefaultAppProfile();

        $email = $validated['email'] ?: createDummyMail();
        $username = $validated['username'];

        $profileNeedsMail = $appProfile->getValue('signup_show_email') && $appProfile->getValue('signup_show_email_mandatory') === 'mandatory';
        $needsMail = !$app->allowMaillessSignup() || $profileNeedsMail || $request->input('email');

        $lockPrefix = 'storeUser-' . $app->id . '-';
        $lockByUsername = null;
        $lockByEmail = null;

        if ($app->uniqueUsernames()) {
            $lockByUsername = Cache::lock($lockPrefix . $username, 5);
        }
        if ($needsMail) {
            $lockByEmail = Cache::lock($lockPrefix . $email, 5);
        }

        $firstname = $validated['firstname'] ?? '';
        $lastname = $validated['lastname'] ?? '';
        $language = $validated['language'] ?? null;
        $password = $validated['password'];
        $active = $validated['active'] ?? false;
        $inputMeta = $validated['meta'] ?? [];

        if ($app->uniqueUsernames()) {
            $lockByUsername = Cache::lock($lockPrefix . $request->input('username'), 5);
            if (!$lockByUsername->get()) {
                abort(403, 'A user with the same username is already in the process of being created');
            }
        }
        if ($needsMail) {
            $lockByEmail = Cache::lock($lockPrefix . $request->input('email'), 5);
            if (!$lockByEmail->get()) {
                abort(403, 'A user with the same email is already in the process of being created');
            }
        }

        DB::beginTransaction();

        $user = new User();
        $user->password = Hash::make($password);
        $user->active = $active;
        $user->username = $username;
        $user->firstname = $firstname;
        $user->lastname = $lastname;
        $user->language = $language;
        $user->app_id = $app->id;
        $user->email = $email;

        $appProfile = $user->getAppProfile();
        if ($appProfile->getValue('signup_force_password_reset', false, true)) {
            $user->force_password_reset = true;
        }

        $user->save();

        if ($inputMeta) {
            $meta = [];
            foreach ($app->getUserMetaDataFields(true) as $key => $metaField) {
                if (isset($inputMeta[$key])) {
                    $meta[$key] = $inputMeta[$key];
                } else {
                    $meta[$key] = '';
                }
            }
            $user->setMeta($meta);
        }

        $tagUpdates = null;

        if ($request->has('tags')) {
            $tagIds = $request->input('tags', []);
            $tagUpdates = $user->tags()->sync($tagIds);
            $user->load(['tags']);
        }

        $accessLogEngine->log(AccessLog::ACTION_USER_ADD, new AccessLogUserAdd($user, $tagUpdates), $apiUser->id);
        AnalyticsEvent::log($user, AnalyticsEvent::TYPE_USER_CREATED);

        DB::commit();

        optional($lockByUsername)->release();
        optional($lockByEmail)->release();

        if (!$user->isMaillessAccount()) {
            $mailer->sendAppInvitation($user->app_id, $user->email, $user->id, $password);
        }

        return response()->json($userTransformer->transform($user), 201);
    }

    /**
     * Updates the user.
     *
     * @param $userId
     * @param UserUpdateFormRequest $request
     * @param UserTransformer $userTransformer
     * @param AccessLogEngine $accessLogEngine
     * @return JsonResponse
     */
    public function update($userId, UserUpdateFormRequest $request, UserTransformer $userTransformer, AccessLogEngine $accessLogEngine)
    {
        $validated = $request->validated();

        $apiUser = Auth::user();
        $app = $apiUser->app;

        $inputMeta = $validated['meta'] ?? [];

        $user = User
            ::where('is_dummy', false)
            ->where('is_api_user', false)
            ->find($userId);

        DB::beginTransaction();

        $basicFields = [
            'email',
            'username',
            'password',
            'firstname',
            'lastname',
            'active',
            'language',
        ];
        foreach ($basicFields as $field) {
            if ($request->has($field)) {
                $value = $validated[$field];

                if ($field === 'password') {
                    $value = Hash::make($value);
                    $appProfile = $user->getAppProfile();
                    if ($appProfile->getValue('signup_force_password_reset', false, true)) {
                        $user->force_password_reset = true;
                    }
                } elseif (is_string($value)) {
                    $value = utrim($value);
                }

                if ($field === 'email' && !$value) {
                    if (isDummyMail($user->email)) {
                        continue;
                    }
                    $value = createDummyMail();
                }

                $user->setAttribute($field, $value);
            }
        }

        $tagUpdates = null;

        if($request->has('tags')) {
            $tagIds = $request->input('tags', []);
            $tagUpdates = $user->tags()->sync($tagIds);
        }

        if ($user->isDirty() || ($tagUpdates !== null && array_filter($tagUpdates))) {
            $accessLogEngine->log(AccessLog::ACTION_USER_UPDATE, new AccessLogUserUpdate($user, $tagUpdates), $apiUser->id);
        }

        $user->save();

        if ($inputMeta) {
            $meta = $user->getMeta();
            foreach ($app->getUserMetaDataFields(true) as $key => $metaField) {
                if(isset($inputMeta[$key])) {
                    $meta[$key] = $inputMeta[$key];
                }
            }
            $user->setMeta($meta);
        }

        DB::commit();

        return response()->json($userTransformer->transform($user));
    }

    /**
     * Deletes the user.
     *
     * @param UserDeleteFormRequest $request
     * @return APIError|JsonResponse
     * @throws \Exception
     */
    public function delete(UserDeleteFormRequest $request) {
        $validated = $request->validated();

        $user = User
            ::where('is_dummy', false)
            ->where('is_api_user', false)
            ->find($validated['resourceId']);

        $result = $user->safeRemove();

        if($result->isSuccessful()) {
            return response()->json([], 204);
        } else {
            return new APIError( 'invalid input', 400);
        }
    }
}
