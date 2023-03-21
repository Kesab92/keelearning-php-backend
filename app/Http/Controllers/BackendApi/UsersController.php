<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\APIError;
use App\Http\Controllers\Controller;
use App\Jobs\DeleteUsers;
use App\Mail\Mailer;
use App\Models\AccessLog;
use App\Models\AnalyticsEvent;
use App\Models\App;
use App\Models\Tag;
use App\Models\User;
use App\Models\UserRole;
use App\Services\AccessLogEngine;
use App\Services\AccessLogMeta\AccessLogUserAdd;
use App\Services\AccessLogMeta\AccessLogUserPasswordReset;
use App\Services\AccessLogMeta\AccessLogUserUpdate;
use App\Services\ConfigurationLogicInvestigator;
use App\Services\NotificationSettingsEngine;
use App\Services\UserEngine;
use App\Traits\PersonalData;
use DB;
use Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Response;

class UsersController extends Controller
{
    use PersonalData;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:,users-edit|users-view')->except('getAdmins');
        $this->middleware('auth.backendaccess:,users-edit')->only([
            'addTags',
            'delete',
            'deleteInformation',
            'deleteTags',
            'getDeletionInformation',
            'reinviteUsers',
            'removeUsers',
            'resetPassword',
            'restore',
            'storeMultiple',
            'update',
        ]);
        $this->personalDataRightsMiddleware('users');
    }

    const ORDER_BY = [
        'id',
        'username',
        'created_at',
        'expires_at_combined',
        'last_activity',
    ];
    const PER_PAGE = [
        50,
        100,
        200,
    ];

    /**
     * @return JsonResponse
     */
    public function index(UserEngine $userEngine)
    {
        $filter = request()->get('filter');
        $orderBy = request()->get('sortBy');
        if (! in_array($orderBy, self::ORDER_BY)) {
            $orderBy = self::ORDER_BY[0];
        }
        $orderDescending = request()->get('descending') === 'true';
        $page = (int) request()->get('page') ?? 1;
        $perPage = request()->get('rowsPerPage');
        if (! in_array($perPage, self::PER_PAGE)) {
            $perPage = self::PER_PAGE[0];
        }
        $search = request()->get('search');
        $tags = request()->get('tags', []);

        $userQuery = $userEngine->userFilterQuery(appId(), $search, $tags, $filter, $orderBy, $orderDescending, $this->showPersonalData, $this->showEmails);

        $userCount = $userQuery->count();
        $users = $userQuery
            ->offset($perPage * ($page - 1))
            ->limit($perPage)
            ->with('tags', 'role', 'role.rights', 'tagRightsRelation')
            ->get()
            ->makeVisible([
                'created_at',
                'is_admin',
            ]);

        if (!$this->showPersonalData) {
            $users->makeHidden([
                'email',
                'firstname',
                'lastname',
            ]);
        }

        if (!$this->showEmails) {
            $users->makeHidden('email');
        }

        $roles = UserRole::where('app_id', appId())->get();

        return response()->json([
            'count' => $userCount,
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    /**
     * @param Request $request
     * @param Mailer $mailer
     * @param AccessLogEngine $accessLogEngine
     * @return APIError|JsonResponse
     * @throws \Throwable
     */
    public function storeMultiple(Request $request, Mailer $mailer, AccessLogEngine $accessLogEngine) {
        $app = App::findOrFail(appId());
        $appProfile = $app->getDefaultAppProfile();
        $users = $request->input('users', []);
        $profileNeedsMail = $appProfile->getValue('signup_show_email') && $appProfile->getValue('signup_show_email_mandatory') === 'mandatory';
        $uniqueMetaFields = $app->getUniqueMetaFields();

        $sendNotificationTo = collect([]);

        DB::beginTransaction();

        foreach ($users as $user) {
            $needsMail = ! $app->allowMaillessSignup() || $profileNeedsMail || $user['email'];
            $meta = [];
            $inputMeta = $user['meta'];
            $user['email'] = utrim(strtolower($user['email']));
            $user['username'] = utrim($user['username']);

            foreach ($app->getUserMetaDataFields(true) as $key => $metaField) {
                if(isset($inputMeta[$key])) {
                    $meta[$key] = $inputMeta[$key];
                } else {
                    $meta[$key] = '';
                }
            }

            if($needsMail && !filter_var($user['email'], FILTER_VALIDATE_EMAIL)) {
                DB::rollBack();
                return new APIError('Bitte geben Sie bei allen Benutzern eine E-Mail Adresse an.');
            }

            if (
                $needsMail
                && User::where('email', $user['email'])->where('app_id', $app->id)->count()
            ) {
                DB::rollBack();
                return new APIError('Email ' . $user['email'] . ' ist bereits vergeben.', 400);
            }

            if ($app->uniqueUsernames() && User::where('username', $user['username'])->where('app_id', $app->id)->count()) {
                DB::rollBack();
                return new APIError('Benutzername ' . $user['username'] . ' ist bereits vergeben.', 400);
            }

            foreach ($uniqueMetaFields as $uniqueMetaField) {
                if ($uniqueMetaField && $meta[$uniqueMetaField]) {
                    $existingUsers = User::getByMetafield($app->id, $uniqueMetaField, $meta[$uniqueMetaField]);
                    if ($existingUsers->count()) {
                        DB::rollBack();
                        return new APIError('Das Meta-Feld ' . $app->getAllUserMetaDataFields()[$uniqueMetaField]['label'] . ' muss einzigartig sein!');
                    }
                }
            }

            $password = randomPassword();

            $newUser = new User();
            $newUser->password = Hash::make($password);
            $newUser->active = 1;
            $newUser->username = $user['username'];
            $newUser->firstname = $user['firstname'];
            $newUser->lastname = $user['lastname'];
            $newUser->language = $user['language'];
            $newUser->app_id = appId();
            $newUser->email = $user['email'] ?: createDummyMail();
            $newUser->save();

            $newUser->setMeta($meta);

            $newTags = $user['tags'];
            $newTags = Tag
                ::where('app_id', $newUser->app_id)
                ->whereIn('id', $newTags)
                ->pluck('id');
            // Check if tags contain same taggroup which have !canHaveDuplicates
            $tagGroupIds = DB::table('tag_groups')
                ->join('tags', 'tag_groups.id', 'tags.tag_group_id')
                ->where('tag_groups.can_have_duplicates', false)
                ->whereIn('tags.id', $newTags)
                ->select('tag_groups.id')
                ->pluck('tag_groups.id');

            if (count($tagGroupIds) !== count(array_unique($tagGroupIds->toArray()))) {
                DB::rollBack();
                return new APIError('Diese TAG-Kombination kann nicht gesetzt werden, da mehrere TAGs aus der gleichen TAG-Gruppe vergeben wurden.');
            }

            $tagUpdates = $newUser->syncTags($newTags->toArray());

            $appProfile = $newUser->getAppProfile();
            if ($appProfile->getValue('signup_force_password_reset', false, true)) {
                $newUser->force_password_reset = true;
            }

            $accessLogEngine->log(AccessLog::ACTION_USER_ADD, new AccessLogUserAdd($newUser, $tagUpdates));

            $newUser->save();

            if (! $newUser->isMaillessAccount()) {
                $sendNotificationTo->push([
                    'app_id' => appId(),
                    'email' => $user['email'],
                    'user_id' => $newUser->id,
                    'password' => $password,
                ]);
            }

            AnalyticsEvent::log($newUser, AnalyticsEvent::TYPE_USER_CREATED);
        }

        DB::commit();

        foreach ($sendNotificationTo as $to) {
            $mailer->sendAppInvitation($to['app_id'], $to['email'], $to['user_id'], $to['password']);
        }

        return Response::json([]);
    }

    public function show($id, NotificationSettingsEngine $notificationSettingsEngine) {
        $user = $this->getUser($id);

        $userData = $this->formatUser($user, $notificationSettingsEngine);

        $availableMailNotifications = $notificationSettingsEngine
            ->getNotificationTypes()
            ->except(0) // remove first entry 'all'
            ->mapWithKeys(function ($notification) {
                return [$notification => __('mail_settings.' . $notification)];
            });

        $metaFields = collect($user->app->getUserMetaDataFields($this->showPersonalData))
            ->map(function($field) {
                return $field['label'];
            });

        $userRole = null;

        if($user->user_role_id) {
            $userRole = UserRole::where('app_id', appId())
                ->with('users')
                ->with('rights')
                ->find($user->user_role_id);
            $userRole = $this->formatUserRole($userRole);
        }

        return Response::json([
            'user' => $userData,
            'availableMailNotifications' => $availableMailNotifications,
            'metaFields' => $metaFields,
            'userRole' => $userRole,
        ]);
    }

    /**
     * Updates a user.
     *
     * @param int $id
     * @param Request $request
     * @param AccessLogEngine $accessLogEngine
     * @return APIError|JsonResponse
     * @throws \Exception
     */
    public function update(int $id, Request $request, AccessLogEngine $accessLogEngine): JsonResponse
    {
        // TODO: use transaction since this can fail halfway through?
        $user = $this->getUser($id);

        $basicFields = ['username', 'active', 'firstname', 'lastname', 'expires_at'];

        if(count($user->app->getLanguages()) > 1) {
            $basicFields[] = 'language';
        }
        if(Auth::user()->isMainAdmin()) {
            $basicFields[] = 'user_role_id';
        }
        if(Auth::user()->isSuperAdmin()) {
            $basicFields[] = 'is_keeunit';
        }
        foreach($basicFields as $field) {
            $value = $request->input($field);
            if($request->has($field)) {
                if($field === 'user_role_id' && $value) {
                    $userRole = UserRole::where('app_id', appId())->findOrFail($value);
                    if($userRole->is_main_admin) {
                        $user->tagRightsRelation()->detach();
                    }
                }
                if (is_string($value)) {
                    $value = utrim($value);
                }
                $user->setAttribute($field, $value);
            }
        }

        if($user->user_role_id) {
            $user->is_admin = true;
        } else {
            $user->is_admin = false;
        }

        if ($request->has('meta')) {
            $submittedMeta = $request->input('meta');
            $uniqueMetaFields = $user->app->getUniqueMetaFields();

            foreach ($uniqueMetaFields as $uniqueMetaField) {
                if ($uniqueMetaField && $submittedMeta[$uniqueMetaField]) {
                    $existingUsers = User::getByMetafield($user->app->id, $uniqueMetaField, $submittedMeta[$uniqueMetaField])
                        ->where('id', '!=', $user->id);
                    if ($existingUsers->count()) {
                        return new APIError('Das Meta-Feld ' . $user->app->getAllUserMetaDataFields()[$uniqueMetaField]['label'] . ' muss einzigartig sein!', 409);
                    }
                }
            }

            $meta = $user->getMeta();
            foreach ($user->app->getUserMetaDataFields($this->showPersonalData) as $metaKey => $metaData) {
                if (isset($submittedMeta[$metaKey])) {
                    $meta[$metaKey] = $submittedMeta[$metaKey];
                }
            }
            $user->setMeta($meta);
        }

        if($this->showEmails) {
            $newEmail = utrim($request->input('email'));
            if($newEmail && $newEmail !== $user->email) {
                if(!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
                    return new APIError('Diese E-Mail Adresse ist nicht gültig.', 422);
                }
                $mailIsTaken = User
                    ::where('email', $newEmail)
                    ->where('app_id', appId())
                    ->butNotThisOne($user->id)
                    ->count();
                if ($mailIsTaken) {
                    return new APIError('Diese E-Mail Adresse ist bereits vergeben.', 409);
                } else {
                    $user->email = $newEmail;
                }
            }
        }

        $tagUpdates = null;
        if ($request->has('tags')) {
            $newTags = $request->input('tags', []);

            $newTags = Tag
                ::where('app_id', $user->app_id)
                ->whereIn('id', $newTags)
                ->pluck('id');
            // Check for duplicate TAGs from exclusive groups
            $tagGroupIds = DB::table('tag_groups')
                ->join('tags', 'tag_groups.id', 'tags.tag_group_id')
                ->where('tag_groups.can_have_duplicates', false)
                ->whereIn('tags.id', $newTags)
                ->select('tag_groups.id')
                ->pluck('tag_groups.id');

            if ($tagGroupIds->count() !== $tagGroupIds->unique()->count()) {
                return new APIError('Diese TAG-Kombination kann nicht gesetzt werden, da mehrere TAGs aus der gleichen TAG-Gruppe vergeben wurden.', 409);
            }

            $tagUpdates = $user->syncTags($newTags->toArray());
        }

        if (Auth::user()->isMainAdmin()) {
            if ($user->role && $user->role->is_main_admin) {
                $user->tagRightsRelation()->detach();
            } else {
                if($request->has('tagRights')) {
                    $user->syncTags($request->input('tagRights', []), 'tagRightsRelation', !$user->tagRightsRelation->count());
                }
            }
        }
        if(!$user->is_admin) {
            $user->tagRightsRelation()->detach();
        }

        if ($user->isDirty() || ($tagUpdates !== null && array_filter($tagUpdates))) {
            $accessLogEngine->log(AccessLog::ACTION_USER_UPDATE, new AccessLogUserUpdate($user, $tagUpdates));
        }

        $user->save();

        // As we updated some data, we want to refresh the user here before we return the new user data
        $user->refresh();

        $userRole = null;

        if($user->user_role_id) {
            $userRole = UserRole::where('app_id', appId())
                ->with('users')
                ->with('rights')
                ->find($user->user_role_id);
            $userRole = $this->formatUserRole($userRole);
        }

        return Response::json([
            'user' => $this->formatUser($user, app(NotificationSettingsEngine::class)),
            'userRole' => $userRole,
        ]);
    }

    public function getWarnings(ConfigurationLogicInvestigator $configurationLogicInvestigator)
    {
        $maxFailedLogin = App::find(appId())->getMaxFailedLoginAttempts();

        return response()->json([
            'warnings' => [
                'failed_login' => User::activeOfApp(appId())->tagRights()->where('failed_login_attempts', '>=', $maxFailedLogin)->count(),
                'powerless_admins' => User::activeOfApp(appId())->tagRights()->powerlessAdmin()->count(),
                'without_category' => $configurationLogicInvestigator->usersCantPlayCategories(appId())->count(),
            ],
        ]);
    }

    public function addTags(AccessLogEngine $accessLogEngine)
    {
        $tags = Tag::ofApp(appId())->whereIn('id', request()->get('tags'))->get();
        $users = User::ofApp(appId())
            ->tagRights()
            ->whereIn('id', request()->get('users'))
            ->get();
        $tagRights = Auth::user()->tagRightsRelation->pluck('id');
        if (! $users->count() || ! $tags->count()) {
            app()->abort(403);
        }

        foreach ($users as $user) {
            $updated = false;
            $updatedTags = [];
            foreach ($tags as $tag) {
                $adminCanUpdateTag = Auth::user()->isFullAdmin() || $tagRights->contains($tag->id);
                if (! $user->tags->contains($tag->id) && $adminCanUpdateTag) {
                    $user->tags()->attach($tag->id);
                    $updated = true;
                    $updatedTags[] = $tag->id;
                }
            }

            if ($updated) {
                $accessLogEngine->log(AccessLog::ACTION_USER_UPDATE, new AccessLogUserUpdate($user, [
                    'attached' => $updatedTags,
                ]));
            }
        }

        return response()->json([
            'success' => true,
        ]);
    }

    public function deleteTags(AccessLogEngine $accessLogEngine)
    {
        $tags = Tag::ofApp(appId())->whereIn('id', request()->get('tags'))->get();
        $users = User::ofApp(appId())
            ->tagRights()
            ->whereIn('id', request()->get('users'))
            ->get();
        $tagRights = Auth::user()->tagRightsRelation->pluck('id');

        if (! $users->count() || ! $tags->count()) {
            app()->abort(403);
        }

        foreach ($users as $user) {
            $updatedTags = [];
            $userTagIds = $user->tags->pluck('id');
            $availableTagIds = $userTagIds->intersect($tagRights);

            foreach ($tags as $tag) {
                $adminCanUpdateTag = Auth::user()->isFullAdmin() || $tagRights->contains($tag->id);

                if($availableTagIds->count() == 1 && !Auth::user()->isFullAdmin()) {
                    $adminCanUpdateTag = false;
                }

                if ($user->tags->contains($tag->id) && $adminCanUpdateTag) {
                    $user->tags()->detach($tag->id);
                    $availableTagIds = $availableTagIds->filter(function ($availableTagId) use($tag) {
                        return $availableTagId != $tag->id;
                    });
                    $updatedTags[] = $tag->id;
                }
            }

            if (count($updatedTags) > 0) {
                $accessLogEngine->log(AccessLog::ACTION_USER_UPDATE, new AccessLogUserUpdate($user, [
                    'detached' => $updatedTags,
                ]));
            }
        }

        return response()->json([
            'success' => true,
        ]);
    }

    public function setExpiration(Request $request, AccessLogEngine $accessLogEngine)
    {
        $this->validate($request, [
            'expires_at' => 'nullable|date',
        ]);

        $expiresAt = $request->get('expires_at');
        $expiresAtDate = null;

        if($expiresAt) {
            $expiresAtDate = Carbon::createFromFormat('Y-m-d', $expiresAt);
        }

        if($expiresAtDate && $expiresAtDate->isPast()) {
            abort(403, 'Löschdatum muss in der Zukunft liegen.');
        }

        $users = User::ofApp(appId())
            ->tagRights()
            ->whereIn('id', $request->get('users'))
            ->get();

        if (!$users->count()) {
            app()->abort(403);
        }

        foreach ($users as $user) {
            $user->expires_at = $expiresAt;
            $accessLogEngine->log(AccessLog::ACTION_USER_UPDATE, new AccessLogUserUpdate($user));
            $user->save();
        }

        return response()->json([
            'success' => true,
        ]);
    }

    public function getDeletionInformation()
    {
        $users = User::where('app_id', appId())
            ->tagRights()
            ->whereIn('id', request()->get('users'))
            ->get();

        $result = [
            'accesslogs' => 0,
            'games' => 0,
            'suggestions' => 0,
            'quizTeams' => 0,
            'voucherCodes' => 0,
            'tags' => 0,
            'testSubmissions' => 0,
            'quizTeamMember' => 0,
            'tag_member' => 0,
            'users' => [],
        ];

        $unremovableUsers = [];
        foreach ($users as $user) {
            if (! $user->canBeDeleted()) {
                $unremovableUsers[$user->username] = $user->getBlockingDependees();
                continue;
            }

            $possibleResults = $user->safeRemoveDependees();
            $result['accesslogs'] += $possibleResults['accesslogs'];
            $result['games'] += $possibleResults['games'];
            $result['suggestions'] += $possibleResults['suggestions'];
            $result['quizTeams'] += $possibleResults['quizTeams'];
            $result['voucherCodes'] += $possibleResults['voucherCodes'];
            $result['tags'] += $possibleResults['tags'];
            $result['testSubmissions'] += $possibleResults['testSubmissions'];
            $result['quizTeamMember'] += $possibleResults['quizTeamMember'];
            $result['tag_member'] += $possibleResults['tag_member'];
            $result['users'][] = $user->getDisplayNameFrontend();
        }

        if (count($unremovableUsers) > 0) {
            return response()->json([
                'success' => false,
                'errors' => $unremovableUsers,
            ]);
        }

        return response()->json([
            'success' => true,
            'info' => $result,
        ]);
    }

    public function removeUsers()
    {
        $users = User::ofApp(appId())
            ->tagRights()
            ->whereIn('id', request()->get('users'))
            ->pluck('id')
            ->map(function ($item) {
                return [$item];
            });

        $additionalData = [
            'creatorId' => Auth::user()->id,
            'appId' => appId(),
        ];
        DeleteUsers::dispatch($additionalData, ['id'], $users, DeleteUsers::MODE_DELETE_ONLY);

        return response()->json([
            'success' => true,
        ]);
    }

    public function reinviteUsers(AccessLogEngine $accessLogEngine, Mailer $mailer)
    {
        $users = User::ofApp(appId())
            ->tagRights()
            ->whereIn('id', request()->get('users'))
            ->get();

        foreach ($users as $user) {
            $password = randomPassword();
            $user->password = Hash::make($password);
            $user->failed_login_attempts = 0;
            $user->save();

            $accessLogEngine->log(AccessLog::ACTION_USER_PASSWORD_RESET, new AccessLogUserPasswordReset($user));

            $mailer->sendAppInvitation($user->app_id, $user->email, $user->id, $password);
        }

        return response()->json([
            'success' => true,
        ]);
    }

    public function resetPassword($id, Mailer $mailer, Request $request, AccessLogEngine $accessLogEngine)
    {
        $user = $this->getUser($id);

        $password = randomPassword();

        \DB::transaction(function() use ($user, $password, $accessLogEngine) {
            $user->password = Hash::make($password);
            $user->failed_login_attempts = 0;

            if ($user->getAppProfile()->getValue('signup_force_password_reset', false, true)) {
                $user->force_password_reset = true;
            }

            $user->save();

            $accessLogEngine->log(AccessLog::ACTION_USER_PASSWORD_RESET, new AccessLogUserPasswordReset($user));
        });

        $mailer->sendAppInvitation($user->app_id, $user->email, $user->id, $password);

        return Response::json([
            'password' => $password,
        ]);
    }

    public function restore($id, AccessLogEngine $accessLogEngine)
    {
        $user = $this->getUser($id);

        $user->deleted_at = null;
        $accessLogEngine->log(AccessLog::ACTION_USER_UPDATE, new AccessLogUserUpdate($user));

        $user->save();

        return Response::json([]);
    }

    public function deleteInformation($id)
    {
        $user = $this->getUser($id);

        return Response::json([
            'dependencies' => $user->safeRemoveDependees(),
            'blockers' => $user->getBlockingDependees(),
        ]);
    }

    public function delete($id) {
        $user = $this->getUser($id);
        $result = $user->safeRemove();

        if($result->isSuccessful()) {
            return Response::json([], 204);
        } else {
            return Response::json($result->getMessages(), 400);
        }
    }

    /**
     * Returns the admins of this app.
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function getAdmins()
    {
        $admins = User::ofApp(appId())
            ->tagRights()
            ->where('is_dummy', false)
            ->where('is_api_user', false)
            ->where('is_admin', true)
            ->get()
            ->transform(function ($admin) {
                $email = $admin->email;
                if (!$this->showEmails) {
                    $email = null;
                }
                return [
                    'id' => $admin->id,
                    'username' => $admin->username,
                    'email' => $email,
                    'isFullAdmin' => $admin->isFullAdmin(),
                    'isMaillessAccount' => $admin->isMaillessAccount(),
                ];
            });

        return Response::json([
            'admins' => $admins,
        ]);
    }

    private function formatUser(User $user, NotificationSettingsEngine $notificationSettingsEngine)
    {
        $user->makeVisible([
            'active',
            'created_at',
            'deleted_at',
            'is_admin',
            'tos_accepted',
            'updated_at',
        ]);

        if (!$this->showPersonalData) {
            $user->makeHidden([
                'email',
                'firstname',
                'lastname',
                'username',
            ]);
        }

        if (!$this->showEmails) {
            $user->makeHidden('email');
        }

        $appProfile = $user->getAppProfile();

        $userData = $user->toArray();
        $userData['avatar'] = $user->avatar_url;
        $userData['is_tmp'] = $user->isTmpAccount();
        $userData['login_suspended'] = $user->loginSuspended();
        $userData['meta'] = [];
        foreach(array_keys($this->appSettings->getApp()->getUserMetaDataFields($this->showPersonalData)) as $metaKey) {
            $userData['meta'][$metaKey] = $user->getMeta($metaKey) ?? '';
        }
        $userData['tags'] = $user->tags->map(function($tag) {
            return $tag->id;
        });
        $userData['tagRights'] = $user->tagRightsRelation->map(function($tag) {
            return $tag->id;
        });

        $userData['mailNotifications'] = [];
        foreach($notificationSettingsEngine->getUserNotificationSettings($user) as $item) {
            $userData['mailNotifications'][$item['notification']] = [
                'enabled' => $item['enabled'],
                'mail_disabled' => $item['mail_disabled'],
                'allowedToDeactivate' => $item['allowedToDeactivate'],
            ];
        }

        $userData['app_profile_id'] = $appProfile->id;

        if ($this->showPersonalData) {
            $userEngine = app(UserEngine::class);
            $userData['qualificationHistories'] = $userEngine->getQualificationHistory($user, Auth::user());
        }

        $userData['vouchers'] = $user->voucherCodes->sortByDesc('cash_in_date')->map(function($voucherCode) {
            return [
                'id' => $voucherCode->voucher->id,
                'name' => $voucherCode->voucher->name,
                'validity_duration' => $voucherCode->voucher->validity_duration,
                'validity_interval' => $voucherCode->voucher->validity_interval,
                'voucherCode' => [
                    'cash_in_date' => $voucherCode->cash_in_date,
                    'code' => $voucherCode->code,
                ],
            ];
        })->values();

        $userData['role'] = $user->role ? $user->role->toArray() : null;

        return $userData;
    }

    private function formatUserRole(UserRole $userRole): array
    {
        $response = $userRole->toArray();
        $response['rights'] = $userRole->rights->pluck('right');
        return $response;
    }

    private function getUser($id)
    {
        $user = User
            ::where('is_dummy', false)
            ->where('is_api_user', false)
            ->tagRights()
            ->findOrFail($id);

        // Check access rights
        if ($user->app_id != appId()) {
            app()->abort(403);
        }

        return $user;
    }
}
