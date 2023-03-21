<?php

namespace App\Stats;

use App\Models\GameQuestionAnswer;
use App\Models\User;
use Carbon\Carbon;

/**
 * Amount of answered questions by user.
 */
class QuizProgress extends Statistic
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    protected function getCacheDuration()
    {
        return 60 * 1;
    }

    /**
     * Answered questions by calendar week.
     *
     * @return int
     */
    protected function getValue()
    {
        $weekCount = 11;
        $progress = [];
        $now = Carbon::now();
        $now->startOfWeek();
        while ($weekCount > 0) {
            $answerCount = GameQuestionAnswer::where('user_id', $this->user->id)
                                           //->where('result', 1)
                                             ->where('created_at', '>=', $now)
                                             ->where('created_at', '<=', $now->copy()->endOfWeek())
                                             ->count();
            $progress[] = [
                'answerCount' => $answerCount,
                'week'        => $now->format('W'),
            ];
            $weekCount -= 1;
            $now->subWeek();
            // we're checking here, because we always want the current week to be calculated
            if ($now < $this->user->created_at) {
                break;
            }
        }

        return array_reverse($progress);
    }

    protected function getCacheKey()
    {
        return 'quiz-progress-of-'.$this->user->id;
    }

    protected function getCacheTags()
    {
        return ['user-'.$this->user->id];
    }
}
