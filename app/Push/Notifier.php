<?php

namespace App\Push;

use App\Jobs\SendMobileNotification;
use App\Models\Game;
use App\Models\User;

class Notifier
{
    public static function sendMessage($text, User $recipient, $data = [], $badgeCount = false)
    {
        if (false && ! live()) {
            return false;
        }
        if ($badgeCount === false) {
            $badgeCount = self::getBadgeCount($recipient);
        }
        SendMobileNotification::dispatch($text, $recipient, $data, $badgeCount);

        return true;
    }

    public static function setBadgeCount(User $recipient, $badgeCount = false)
    {
        self::sendMessage('', $recipient, [], $badgeCount);
    }

    public static function getBadgeCount(User $recipient)
    {
        return Game::where(function ($query) use ($recipient) {
            $query->where('player1_id', $recipient->id)
                ->where('status', Game::STATUS_TURN_OF_PLAYER_1);
        })->orWhere(function ($query) use ($recipient) {
            $query->where('player2_id', $recipient->id)
                  ->where('status', Game::STATUS_TURN_OF_PLAYER_2);
        })
        ->count();
    }
}
