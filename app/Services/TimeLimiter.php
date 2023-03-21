<?php

namespace App\Services;

use App\Http\APIError;
use App\Models\GameQuestionAnswer;
use Carbon\Carbon;

class TimeLimiter
{
    public static $fallbackAvailableTime = 40; // In seconds
    public static $graceTime = 10; // In seconds

    /**
     * The function checks if the gameQuestionAnswer was created less than 40 seconds ago.
     *
     * @param GameQuestionAnswer $gameQuestionAnswer
     * @return APIError|bool
     */
    public static function answerGivenWithinTime(GameQuestionAnswer $gameQuestionAnswer)
    {
        $availableTime = $gameQuestionAnswer->gameQuestion->question->realanswertime;
        if (! $availableTime) {
            $availableTime = self::$fallbackAvailableTime;
        }

        // If this field is null, everything is nice. Return true if the empty answer was created less than 40 seconds ago.
        if ($gameQuestionAnswer->result == null) {
            // Check if "now" is after (the time the answer was created + available time)
            return Carbon::now()->lt(Carbon::parse($gameQuestionAnswer->created_at)->addSeconds($availableTime)->addSeconds(self::$graceTime));
        } else {
            // The answer was not null -> the user already answered the question
            return new APIError(__('errors.question_already_answered'));
        }
    }
}
