<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Mail\Mailer;
use App\Models\DirectMessage;
use App\Models\User;
use App\Transformers\BackendApi\DirectMessages\DirectMessageListTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class DirectMessagesController extends Controller
{
    const ORDER_BY = [
        'updated_at',
        'body',
    ];

    const PER_PAGE = [
        20,
        40,
        60,
    ];

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:,users-edit');
    }

    /**
     * @param $id
     * @param Request $request
     * @param Mailer $mailer
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function store($id, Request $request, Mailer $mailer) {
        $user = $this->getUser($id);

        $directMessage = new DirectMessage();
        $directMessage->app_id = appId();
        $directMessage->recipient_id = $id;
        $directMessage->sender_id = Auth::user()->id;
        $directMessage->body = $request->input('body');
        $directMessage->save();

        $mailer->sendDirectMessage($user, $request->input('body'));

        return Response::json([]);
    }

    /**
     * @param $id
     * @return User
     * @throws \Exception
     */
    private function getUser($id)  {
        $user = User
            ::where('is_dummy', false)
            ->where('is_api_user', false)
            ->findOrFail($id);

        // Check access rights
        if ($user->app_id != appId()) {
            app()->abort(403);
        }

        return $user;
    }
    /**
     * @param int $id
     * @param DirectMessageListTransformer  $directMessageListTransformer
     * @return JsonResponse
     */
    public function index(int $id, Request $request, DirectMessageListTransformer $directMessageListTransformer){

        $orderBy = request()->get('sortBy');
        if (! in_array($orderBy, self::ORDER_BY)) {
            $orderBy = self::ORDER_BY[0];
        }

        $orderDescending = request()->get('descending') === 'true'? 'asc' : 'desc';

        $page = (int) $request->input('page') ?? 1;
        $perPage = $request->input('rowsPerPage');
        if (! in_array($perPage, self::PER_PAGE)) {
            $perPage = self::PER_PAGE[0];
        }
        $this->getUser($id);

        $messageCount = DirectMessage
            ::where('recipient_id',$id)
            ->count();
        $messages = DirectMessage
            ::where('recipient_id',$id)
            ->orderBy($orderBy ,$orderDescending)
            ->offset($perPage * ($page - 1))
            ->limit($perPage)
            ->get();

        return response()->json([
            'messageCount' => $messageCount,
            'messages' => $directMessageListTransformer->transformAll($messages),
        ]);
    }
}
