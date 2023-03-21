<?php

namespace Tests;

use App\Mail\Mailer;
use App\Models\Competition;
use App\Models\QuizTeam;
use App\Models\QuizTeamMember;
use App\Models\User;
use App\Services\Referee;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class MailerTest extends TestCase
{
    private $mailer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mailer = new Mailer();
    }

    public function testSendCompetitionResults()
    {
        $users = [];

        $group = new QuizTeam();
        $group->app_id = 1;
        $group->name = 'Die swaggigen yolos';
        $group->owner_id = 2;
        $group->save();

        $competition = new Competition();
        $competition->app_id = 1;
        $competition->category_id = 1;
        $competition->group_id = $group->id;
        $competition->duration = 2;
        $competition->save();

        for ($i = 0; $i < 20; $i++) {
            $user = new User();
            $user->app_id = 1;
            $user->username = 'User #'.($i + 1);
            $user->email = ($i + 1).'test@mail.com';
            $user->password = Hash::make('swag');
            $user->tos_accepted = 1;
            $user->save();

            $groupUser = new QuizTeamMember();
            $groupUser->group_id = $group->id;
            $groupUser->user_id = $user->id;
            $groupUser->save();

            $users[] = [
                'userId' => $user->id,
                'rightAnswers' => 20 - $i,
            ];
        }

        // Ad myself as owner of that group 8)
        $groupUser = new QuizTeamMember();
        $groupUser->group_id = $group->id;
        $groupUser->user_id = 2;
        $groupUser->save();

        $users[] = [
            'userId' => 2,
            'rightAnswers' => 5,
        ];

        $this->mailer->sendCompetitionResults($users, $group->id, $competition->getCategoryName());
    }

    public function testCheckCompetitionIsEnded()
    {
        $group = new QuizTeam();
        $group->app_id = 1;
        $group->name = 'Die yolos';
        $group->owner_id = 2;
        $group->save();

        $competition = new Competition();
        $competition->app_id = 1;
        $competition->category_id = 1;
        $competition->group_id = $group->id;
        $competition->duration = 2;
        $competition->save();

        for ($i = 0; $i < 5; $i++) {
            $user = new User();
            $user->app_id = 1;
            $user->username = 'User #'.($i + 1);
            $user->email = ($i + 1).'test@mail.com';
            $user->password = Hash::make('swag');
            $user->tos_accepted = 1;
            $user->save();

            $groupUser = new QuizTeamMember();
            $groupUser->group_id = $group->id;
            $groupUser->user_id = $user->id;
            $groupUser->save();
        }

        // Add me as owner 8)
        $groupUser = new QuizTeamMember();
        $groupUser->group_id = $group->id;
        $groupUser->user_id = 2;
        $groupUser->save();

        $this->assertFalse(Referee::competitionIsEnded($competition->id));

        $competition->created_at = Carbon::now()->subDays(5);
        $competition->save();

        $this->assertTrue(Referee::competitionIsEnded($competition->id));

        Referee::seekAndFinishCompetition($competition->id);
    }

    public function testNothingShouldBeSentInvitation()
    {
        Log::info('+++++++++++++++++++ INVISIBLE PART +++++++++++++++++++++++');

        $game = new \App\Models\Game();
        $game->player1_id = 1;
        $game->player2_id = 2;
        $game->app_id = 1;
        $game->save();

        $player2 = User::find(2);
        $player2->active = false;
        $player2->save();

        Log::info('** ');
        Log::info('Invitation Player 2');
        $this->mailer->sendInvitation($game->id);
        Log::info('** ');

        // Reset
        $player2->active = true;
        $player2->save();

        $game->delete();
    }

    public function testNothingShouldBeSentReset()
    {
        Log::info('+++++++++++++++++++ INVISIBLE PART +++++++++++++++++++++++');

        $game = new \App\Models\Game();
        $game->player1_id = 1;
        $game->player2_id = 2;
        $game->app_id = 1;
        $game->save();

        $player2 = User::find(2);
        $player2->active = false;
        $player2->save();

        Log::info('** ');
        Log::info('Invitation Player 2');
        $this->mailer->sendInvitation($game->id);
        Log::info('** ');

        // Reset
        $player2->active = true;
        $player2->save();

        $game->delete();
    }
}
