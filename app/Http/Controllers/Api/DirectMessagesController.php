<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DirectMessage;
use Illuminate\Support\Carbon;
use Response;

class DirectMessagesController extends Controller
{
    /**
     * Returns direct messages for an user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function directMessages() {
        $user = user();

        $directMessages = DirectMessage::where('recipient_id', $user->id)
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->transform(function (DirectMessage $directMessage) {
            return [
                'id' => $directMessage->id,
                'body' => $directMessage->body,
            ];
        });

        return Response::json(['directMessages' => $directMessages]);
    }

    /**
     * Marks the direct message as read.
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead($id) {
        $user = user();
        $directMessage = DirectMessage::where('id', $id)
            ->firstOrFail();

        if($directMessage->recipient_id !== $user->id) {
            abort(404);
        }

        $directMessage->read_at = Carbon::now();
        $directMessage->save();

        return Response::json([]);
    }
}
