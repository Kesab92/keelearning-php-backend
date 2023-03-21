<?php

namespace App\Http\Controllers\Api;

use App\Http\APIError;
use App\Http\Controllers\Controller;
use App\Services\LikesEngine;
use Exception;
use Illuminate\Http\JsonResponse;
use Response;

class LikesController extends Controller
{
    /**
     * @var LikesEngine
     */
    private LikesEngine $likesEngine;

    public function __construct(LikesEngine $likesEngine)
    {
        $this->likesEngine = $likesEngine;
        parent::__construct();
    }

    /**
     * Checks if the user likes a resource.
     *
     * @param $foreign_type
     * @param $foreign_id
     * @return JsonResponse
     */
    public function likesIt($foreign_type, $foreign_id)
    {
        // We don't need to check access rights here, because for resources where the user doesn't have access
        // this will just always return false
        $user = user();
        $foreign_type = (int) $foreign_type;
        $foreign_id = (int) $foreign_id;

        return Response::json([
            'likes_it' => $this->likesEngine->likesIt($user, $foreign_type, $foreign_id),
        ]);
    }

    /**
     * @param $foreign_type
     * @param $foreign_id
     * @return APIError|JsonResponse
     * @throws Exception
     */
    public function like($foreign_type, $foreign_id)
    {
        $user = user();
        $foreign_type = (int) $foreign_type;
        $foreign_id = (int) $foreign_id;

        $resource = $this->likesEngine->getResource($foreign_type, $foreign_id);
        if (! $this->likesEngine->hasAccess($user, $resource)) {
            return new APIError('You have no access to this resource', 403);
        }

        return Response::json([
            'success' => $this->likesEngine->like($user, $foreign_type, $foreign_id),
        ]);
    }

    /**
     * @param $foreign_type
     * @param $foreign_id
     * @return APIError|JsonResponse
     * @throws Exception
     */
    public function dislike($foreign_type, $foreign_id)
    {
        $user = user();
        $foreign_type = (int) $foreign_type;
        $foreign_id = (int) $foreign_id;

        // No need to check access here, because the user can only control likes they already set in the past

        return Response::json([
            'success' => $this->likesEngine->dislike($user, $foreign_type, $foreign_id),
        ]);
    }
}
