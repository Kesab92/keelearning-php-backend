<?php

namespace App\Services;

use App\Models\AppRating;

class RatingEngine
{
    /**
     * Creates a rating based on the user.
     * @param $rating
     * @param $userId
     * @return bool Returns true if creation was successful and false if it was not.
     */
    public function setRating($rating, $userId)
    {
        $appRating = AppRating::where('user_id', $userId)->first();
        if (! $appRating) {
            $appRating = new AppRating();
            $appRating->user_id = $userId;
        }
        $appRating->rating = $rating;
        $appRating->save();

        return true;
    }
}
