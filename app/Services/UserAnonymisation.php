<?php

namespace App\Services;

use App\Models\AccessLog;
use App\Models\AnalyticsEvent;
use App\Models\AppRating;
use App\Models\Comments\Comment;
use App\Models\Comments\CommentReport;
use App\Models\Game;
use App\Models\GameQuestionAnswer;
use App\Models\QuizTeam;
use App\Models\SuggestedQuestion;
use App\Models\Tag;
use App\Models\TestSubmission;
use App\Models\User;
use App\Models\VoucherCode;

class UserAnonymisation
{
    private $overwrite = [
        AccessLog::class => [
            'user_id',
        ],
        AnalyticsEvent::class => [
            'user_id',
        ],
        AppRating::class => [
            'user_id',
        ],
        Comment::class => [
            'deleted_by_id',
            'author_id',
        ],
        CommentReport::class => [
            'reporter_id',
        ],
        GameQuestionAnswer::class => [
            'user_id',
        ],
        Game::class => [
            'player1_id',
            'player2_id',
        ],
        QuizTeam::class => [
            'owner_id',
        ],
        SuggestedQuestion::class => [
            'user_id',
        ],
        Tag::class => [
            'creator_id',
        ],
        TestSubmission::class => [
            'user_id',
        ],
        VoucherCode::class => [
            'user_id',
        ],
    ];

    public function anonymiseUser(User $user)
    {
        $dummy = User::where('app_id', $user->app_id)
                             ->where('is_dummy', true)
                             ->first();
        if (! $dummy) {
            throw new \Exception('No dummy user set for app #'.$user->app_id.'!');
        }
        foreach ($this->overwrite as $class => $columns) {
            foreach ($columns as $column) {
                $class::where($column, $user->id)
                      ->update([
                        $column => $dummy->id,
                      ]);
            }
        }

        return $user->safeRemove();
    }
}
