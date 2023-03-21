<?php

namespace App\Services;

use App\Mail\Mailer;
use App\Models\GameQuestionAnswer;
use App\Models\User;
use Carbon\Carbon;

class UsageReminder
{
    const IDLE_DAYS_THRESHOLDS = [
        5,
        10,
        30,
    ];

    /**
     * The function determines, if the last answer of a user is too far distant in the past.
     *
     * @param User $user
     * @return bool
     */
    public static function userIsIdleForTooLong(User $user)
    {
        // Get the last made answers of the user
        $lastGameQuestionAnswers = GameQuestionAnswer::ofUser($user->id)
            ->orderBy('id', 'DESC');

        // Get either the last answer creation time or the time the user was created
        if ($lastGameQuestionAnswers->count() > 0) {
            $lastGameQuestionAnswer = $lastGameQuestionAnswers->first();
            $then = Carbon::parse($lastGameQuestionAnswer->created_at);
        } else {
            $then = Carbon::parse($user->created_at);
        }

        // The user has to be reminded
        foreach (self::IDLE_DAYS_THRESHOLDS as $threshold) {
            if (Carbon::now()->subDays($threshold)->format('Y-m-d') == $then->format('Y-m-d')) {
                return true;
            }
        }

        return false;
    }

    /**
     * The function sends an email to a user that is idle for too long.
     *
     * @param User $user
     * @param GameEngine $gameEngine
     * @param Mailer $mailer
     * @param $appId
     * @param $userId
     * @return bool
     */
    public static function seekAndRemindUser(User $user, GameEngine $gameEngine, Mailer $mailer, $appId, $userId)
    {
        if (self::userIsIdleForTooLong($user)) {
            $gameEngine->sendReminder($mailer, $appId, $userId);

            return true;
        }

        return false;
    }
}
