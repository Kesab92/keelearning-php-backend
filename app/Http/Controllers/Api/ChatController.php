<?php

namespace App\Http\Controllers\Api;

use App\Events\GameChatMessage;
use App\Http\APIError;
use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Page;
use App\Models\User;
use App\Services\DoorKeeper;
use Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis as Redis;
use Illuminate\Support\Facades\Request as Input;
use Response;

class ChatController extends Controller
{
    /**
     * Sends a message.
     *
     * @param       $channel
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function send($channel)
    {
        if ($access = $this->checkChannelAccess($channel) !== true) {
            return $access;
        }

        $data = [
            'c' => $channel,
            'm' => Input::get('message'),
            't' => time(),
            'u' => user()->id,
        ];
        $redis = Redis::connection('chat');

        $redis->rPush('gamechat:'.$channel, json_encode($data));
        Event::fire(new GameChatMessage($data));

        return Response::json(['status'=>'success']);
    }

    /**
     * Gets all message from the game.
     *
     * @param       $channel
     *
     * @return APIError|\Illuminate\Http\JsonResponse
     */
    public function get($channel)
    {
        if ($access = $this->checkChannelAccess($channel) !== true) {
            return $access;
        }

        $redis = Redis::connection('chat');
        $messages = $redis->lRange('gamechat:'.$channel, 0, -1);

        $messages = array_map(function ($m) {
            return json_decode($m);
        }, $messages);

        return Response::json($messages);
    }

    public function auth()
    {
        if ($access = $this->checkChannelAccess(Input::get('channel_name')) !== true) {
            return $access;
        }
    }

    /**
     * Checks if the user is allowed to access the given channel.
     *
     * @param $channel
     *
     * @return APIError|bool
     */
    private function checkChannelAccess($channel)
    {
        $expl = explode('-', $channel);
        $user1 = intval($expl[2]);
        $user2 = intval($expl[3]);

        // Check if the user is one of the players of that game. Else give him nothing
        if (! in_array(user()->id, [$user1, $user2])) {
            return new APIError(__('errors.chat_not_allowed'), 403);
        }

        return true;
    }
}
