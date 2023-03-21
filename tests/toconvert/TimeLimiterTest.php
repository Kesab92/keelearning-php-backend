<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class TimeLimiterTest extends TestCase
{
    public function testCheckIfAnswerCameWithinTime()
    {
        $gameQuestionAnswer = new \App\Models\GameQuestionAnswer();
        $gameQuestionAnswer->game_question_id = 42;
        $gameQuestionAnswer->user_id = 2;
        $gameQuestionAnswer->question_answer_id = null;
        $gameQuestionAnswer->save();

        $this->assertTrue(\App\Services\TimeLimiter::answerGivenWithinTime($gameQuestionAnswer));

        $gameQuestionAnswer->created_at = \Carbon\Carbon::now()->subSeconds(41);
        $gameQuestionAnswer->save();

        $this->assertFalse(\App\Services\TimeLimiter::answerGivenWithinTime($gameQuestionAnswer));

        $gameQuestionAnswer->delete();
    }
}
