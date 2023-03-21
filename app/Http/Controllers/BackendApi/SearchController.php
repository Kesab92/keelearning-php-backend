<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use App\Models\User;
use App\Traits\PersonalData;
use Auth;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Response;

class SearchController extends Controller
{
    use PersonalData;

    const ALLOWED_MODULES = [
        'quizteams',
        'webinars',
        'courses',
    ];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns a JSON of users that have a name or email that fits the query.
     *
     * @param Request     $request
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function users($module, Request $request)
    {
        if (!in_array($module, self::ALLOWED_MODULES)) {
            return app()->abort(403);
        }
        $this->checkPersonalDataRights($module, Auth::user());
        $query = $request->input('q');
        $users = User::activeOfApp(appId())
            ->tagRights()
            ->where(function ($q) use ($query) {
                $q->whereRaw('username LIKE ?', '%'.escapeLikeInput($query).'%');
                if ($this->showEmails) {
                    $q->orWhereRaw('email LIKE ?', '%'.escapeLikeInput($query).'%');
                }
            })
            ->take(7)
            ->get();

        $results = [];
        /** @var User $user */
        foreach ($users as $user) {
            $userData = [
                'id' => $user->id,
                'username' => $user->username,
                'fullName' => '',
                'title' => $user->username,
                'image' => $user->avatar_url,
                'email' => $this->showEmails ? $user->getMailBackend() : null
            ];
            if ($this->showPersonalData) {
                $userData['title'] = $user->getDisplayNameBackend($this->showEmails);
                $userData['fullName'] = $user->getFullName();
            }
            $results[] = $userData;
        }

        return response()->json(['results' => $results]);
    }

    /**
     * Returns a JSON of users for a list of user ids
     *
     * @param Request     $request
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function usersFromIds($module, Request $request): JsonResponse
    {
        if (!in_array($module, self::ALLOWED_MODULES)) {
            return app()->abort(403);
        }
        $this->checkPersonalDataRights($module, Auth::user());

        $userIds = $request->input('userIds', []);
        $users = User::ofApp(appId())
            ->with('tags')
            ->whereIn('id', $userIds)
            ->get();

        $results = [];
        /** @var User $user */
        foreach ($users as $user) {
            $results[] = [
                'id' => $user->id,
                'email' => $this->showEmails ? $user->getMailBackend() : null,
                'username' => $user->username,
                'readonly' => !$user->isAccessibleByAdmin(),
            ];
        }

        return response()->json(['results' => $results]);
    }

    /**
     * Gets the number of users with the given tags.
     *
     * @param string $tag_ids Comma separated list of tag ids
     * @return JsonResponse
     * @throws \Exception
     */
    public function tagsUserCount($tag_ids = null)
    {
        // only count tags & users belonging to current app
        if ($tag_ids) {
            $tagIds = Tag::ofApp(appId())->whereIn('id', explode(',', $tag_ids))->pluck('id');
        }
        if (! $tag_ids || ! $tagIds->count()) {
            // no tags? -> all users
            return Response::json([
                'users_count' => User::activeOfApp(appId())->count(),
            ]);
        }

        // additional users which might or might not have the given tags,
        // but should be selected too
        if (request()->get('user_ids')) {
            $userIds = User::activeOfApp(appId())
                ->whereIn('id', request()->get('user_ids'))
                ->pluck('id');
        }

        $userCountQuery = DB::table('users')
            ->where('app_id', appId())
            ->where('active', true)
            ->whereNull('deleted_at');
        if (request()->get('user_ids')) {
            $userCountQuery->whereNotIn('users.id', $userIds);
        }
        $userCount = $userCountQuery->join('tag_user', 'tag_user.user_id', 'users.id')
            ->whereIn('tag_user.tag_id', $tagIds)
            ->selectRaw('COUNT(DISTINCT users.id) as count')
            ->first()
            ->count;

        if (request()->get('user_ids')) {
            $userCount += $userIds->count();
        }

        return Response::json([
            'users_count' => $userCount,
        ]);
    }
}
