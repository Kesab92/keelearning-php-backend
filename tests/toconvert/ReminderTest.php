<?php

namespace Tests;

use App\Mail\Mailer;
use App\Models\GameQuestionAnswer;
use App\Models\User;
use App\Services\GameEngine;
use App\Services\UsageReminder;
use Carbon\Carbon;

class ReminderTest extends TestCase
{
    public function testUserIsIdleForTooLong()
    {
        $gqa = new GameQuestionAnswer();
        $gqa->game_question_id = 42;
        $gqa->question_answer_id = 42;
        $gqa->user_id = 2;
        $gqa->save();

        $user = User::find(2);
        $gameQuestionAnswers = GameQuestionAnswer::ofUser(2)->orderBy('id', 'DESC');
        $this->assertTrue($gameQuestionAnswers->count() > 0);

        $gameQuestionAnswer = $gameQuestionAnswers->first();
        $this->assertFalse(UsageReminder::userIsIdleForTooLong($user));

        $now = Carbon::now();
        $then = $now->subDays(10);

        $gameQuestionAnswer->created_at = $then;
        $gameQuestionAnswer->save();

        $this->assertTrue(UsageReminder::userIsIdleForTooLong($user));
    }

    public function testPlayerWIthoutAnswers()
    {
        $user = new User();
        $user->username = 'jbsdfjb';
        $user->active = 1;
        $user->email = 'ljsdnf@ksdnf.de';
        $user->password = 'lsdnf';
        $user->tos_accepted = 1;
        $user->app_id = 1;
        $user->save();

        $this->assertTrue($user->created_at != null);
        $this->assertFalse(UsageReminder::userIsIdleForTooLong($user));

        $user->created_at = Carbon::now()->subDays(10);
        $user->save();

        $this->assertTrue(UsageReminder::userIsIdleForTooLong($user));
        Log::info('???????????????????????????????????');
        Log::info('This should be before the usage reminder mail');
        $gameEngine = new GameEngine();
        $mailer = new Mailer();
        $this->assertTrue(UsageReminder::seekAndRemindUser($user, $gameEngine, $mailer, 1, $user->id));

        // Check if an inactive user is not reminded
        $user->active = 0;
        $user->save();

        $this->assertFalse(UsageReminder::seekAndRemindUser($user, $gameEngine, $mailer, 1, $user->id));

        Log::info('???????????????????????????????????');

        $user->delete();
    }
}
