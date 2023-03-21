<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Models\UserRole;
use App\Models\UserRoleRight;
use App\Models\AccessLog;
use App\Services\AccessLogEngine;
use App\Services\AccessLogMeta\UserRoles\AccessLogUserRoleCreate;
use App\Services\AccessLogMeta\UserRoles\AccessLogUserRoleUpdate;
use App\Services\AccessLogMeta\UserRoles\AccessLogUserRoleDelete;
use App\Http\Requests\BackendApi\UserRole\UserRoleCreateRequest;
use App\Http\Requests\BackendApi\UserRole\UserRoleUpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserRolesController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.mainadmin');
    }

    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $userRoles = UserRole::ofApp(appId())
            ->withCount('users')
            ->get();

        return response()->json([
            'userRoles' => $userRoles,
        ]);
    }

    /**
     * @param int $userRoleId
     * @return JsonResponse
     */
    public function show(int $userRoleId): JsonResponse
    {
        $userRole = UserRole::where('app_id', appId())
            ->with('users')
            ->with('rights')
            ->findOrFail($userRoleId);

        return response()->json([
            'userRole' => $this->getUserRoleResponse($userRole),
        ]);
    }

    /**
     * @param UserRoleCreateRequest $request
     * @param AccessLogEngine $accessLogEngine
     * @return JsonResponse
     */
    public function create(UserRoleCreateRequest $request, AccessLogEngine $accessLogEngine): JsonResponse
    {
        $userRole = DB::transaction(function() use ($accessLogEngine,$request){
        $userRole = new UserRole();
        $userRole->app_id = appId();
        $userRole->name = $request->name;
        $userRole->save();
        $accessLogEngine->log(AccessLog::ACTION_USER_ROLE_CREATE, new AccessLogUserRoleCreate($userRole));

        return $userRole;
    });
        return response()->json([
            'userRoleId' => $userRole->id,
        ]);
    }

    /**
     * @param int $userRoleId
     * @param UserRoleUpdateRequest $request
     * @param AccessLogEngine $accessLogEngine
     * @return JsonResponse
     * @throws \Exception
     */
    public function update(int $userRoleId, UserRoleUpdateRequest $request, AccessLogEngine $accessLogEngine): JsonResponse
    {
        $userRole = DB::transaction(function () use ($userRoleId, $accessLogEngine, $request){
            $userRole = UserRole
                ::where('app_id', appId())
                ->with('rights')
                ->with('users')
                ->findOrFail($userRoleId);

            $oldUserRole = $this->getUserRoleResponse($userRole);

            $basicFields = ['name', 'description'];
            foreach($basicFields as $field) {
                if($request->has($field)) {
                    $value = $request->input($field, null);
                    $userRole->setAttribute($field, $value);
                }
                if($field === 'name') {
                    if(UserRole::where('app_id', appId())->where('id', '!=', $userRoleId)->where('name', $userRole->name)->exists()) {
                        abort(403);
                    }
                }
            }

            if($request->has('rights')) {
                $newRights = collect($request->rights);
                $currentRights = $userRole->rights->pluck('right');

                foreach (UserRoleRight::RIGHT_TYPES as $type) {
                    if ($newRights->contains($type)) {
                        if (!$currentRights->contains($type)) {
                            $userRole->rights()->create([
                                'right' => $type,
                            ]);
                        }
                    } else {
                        $userRole->rights()->where('right', $type)->delete();
                    }
                }
                $userRole->refresh();
            }

            $userRole->save();

            $newUserRole=$this->getUserRoleResponse($userRole);
            $accessLogUserRoleUpdate = new AccessLogUserRoleUpdate($oldUserRole, $newUserRole);
            if($accessLogUserRoleUpdate->hasDifferences()){
                $accessLogEngine->log(AccessLog::ACTION_USER_ROLE_UPDATE, $accessLogUserRoleUpdate);
            }
            return $userRole;
        });

        return response()->json([
            'userRole' => $this->getUserRoleResponse($userRole),
        ]);
    }

    /**
     * @param int $userRoleId
     * @param AccessLogEngine $accessLogEngine
     * @return JsonResponse
     */
    public function clone(int $userRoleId,AccessLogEngine $accessLogEngine): JsonResponse
    {
        $userRole = DB::transaction(function () use ($userRoleId, $accessLogEngine){
            $userRole = UserRole::where('app_id', appId())
                        ->findOrFail($userRoleId);

            if($userRole->is_main_admin) {
                abort(403);
            }

            $newUserRoleName = 'Kopie von ' . $userRole->name;


        if(UserRole::where('app_id', appId())->where('name', $newUserRoleName)->exists()) {
            abort(403, 'Ein Eintrag für "' . $newUserRoleName . '" existiert bereits');
        }


            $newUserRole = $userRole->duplicate();
            $newUserRole->name = $newUserRoleName;
            $newUserRole->save();

            $accessLogEngine->log(AccessLog::ACTION_USER_ROLE_CREATE, new AccessLogUserRoleCreate($newUserRole));

            return $newUserRole;
       });

       return response()->json([
           'user_role_id' => $userRole->id,
       ]);
    }

    /**
     * Returns dependencies and blockers
     *
     * @param int $userRoleId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteInformation(int $userRoleId):JsonResponse
    {
        $userRole = UserRole::where('app_id', appId())
            ->findOrFail($userRoleId);

        return response()->json([
            'dependencies' => $userRole->safeRemoveDependees(),
            'blockers' => $userRole->getBlockingDependees(),
        ]);
    }

    /**
     * Deletes the user role
     *
     * @param int $userRoleId
     * @param AccessLogEngine $accessLogEngine
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(int $userRoleId, AccessLogEngine $accessLogEngine):JsonResponse {
        $userRole = UserRole::where('app_id', appId())
            ->findOrFail($userRoleId);
        $result = DB::transaction(function() use ($accessLogEngine, $userRole) {
            return  $userRole->safeRemove();
        });
        if($result->isSuccessful()) {
            $accessLogEngine->log(AccessLog::ACTION_DELETE_USER_ROLE, new AccessLogUserRoleDelete($userRole), Auth::user()->id);
            return response()->json([], 204);
        } else {
            return response()->json($result->getMessages(), 400);
        }
    }

    private function getUserRoleResponse(UserRole $userRole): array
    {
        $response = $userRole->toArray();
        $response['users'] = $userRole->users->map->only([
                'id',
                'username',
            ])->toArray();
        $response['rights'] = $userRole->rights->pluck('right');
        return $response;
    }
}
