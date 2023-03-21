<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationSubscription;
use App\Models\User;

class NotificationSubscriptionsController extends Controller
{
    public function unsubscribe(int $userId, int $foreignType, int $foreignId)
    {
        $user =  User::findOrFail($userId);
        $deletedRows = NotificationSubscription::where('user_id', $userId)
            ->where('foreign_type', $foreignType)
            ->where('foreign_id', $foreignId)
            ->delete();
        $appProfile = $user->getAppProfile();

        return view('app-message', [
            'appProfile' => $appProfile,
            'isError' => !$deletedRows,
            'message' => __($deletedRows ? 'app_message.unsubscribe_successful' : 'app_message.unsubscribe_not_subscribed'),
        ]);
    }
}
