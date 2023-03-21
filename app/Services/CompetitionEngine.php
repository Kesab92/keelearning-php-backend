<?php
namespace App\Services;

use App\Models\Competition;
use App\Stats\PlayerCorrectAnswersByCategoryBetweenDates;
use App\Stats\PlayerGameWinsBetweenDates;

class CompetitionEngine {
    public function getMemberStats(Competition $competition, $tagIds = null) {
        $members = $competition->members();

        if ($tagIds) {
            $tagIds = explode(',', $tagIds);
            $members = $members->filter(function ($member) use ($tagIds) {
                return $member->tags->whereIn('id', $tagIds)->count() > 0;
            });
        }

        if ($competition->category_id === null) {
            PlayerGameWinsBetweenDates::preload($competition->app_id, $competition->start_at, $competition->getEndDate());
        }
        PlayerCorrectAnswersByCategoryBetweenDates::preload($competition->app_id, $competition->start_at, $competition->getEndDate(), $competition->category_id);

        $members->map(function ($user) use ($competition) {
            $user->stats = array_merge(['answersCorrect' => 0], ['wins']);

            if ($competition->hasStartDate()) {
                if ($competition->category_id === null) {
                    $user->stats = [
                        'answersCorrect' => (new PlayerCorrectAnswersByCategoryBetweenDates($user->id, $competition->category_id, $competition->start_at, $competition->getEndDate()))->noCache()->fetch(),
                        'wins' => (new PlayerGameWinsBetweenDates($user->id, $competition->start_at, $competition->getEndDate()))->noCache()->fetch(),
                    ];
                } else {
                    $user->stats = [
                        'answersCorrect' => (new PlayerCorrectAnswersByCategoryBetweenDates($user->id, $competition->category_id, $competition->start_at, $competition->getEndDate()))->noCache()->fetch(),
                    ];
                }
            }
        });
        $members = $members->sortByDesc(function ($a) {
            return $a->stats['answersCorrect'];
        });

        return $members;
    }
}
