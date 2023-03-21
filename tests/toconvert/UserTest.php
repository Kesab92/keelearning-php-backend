<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class UserTest extends TestCase
{
    public function testUserIsPlayer1Or2()
    {
        $user = \App\Models\User::find(2);
        $game = \App\Models\Game::find(1);

        $response = $user->isPlayer1Or2($game, 2);

        $isRight = false;
        if ($response['userIsPlayer1'] || $response['userIsPlayer2']) {
            $isRight = true;
        }

        $this->assertTrue($isRight);
    }

    public function testUserIsAllowedToPlay()
    {
        $game = \App\Models\Game::find(1);

        // This should be true
        $game->status = \App\Models\Game::STATUS_TURN_OF_PLAYER_1;
        $game->save();

        $this->assertTrue(\App\Models\User::isAllowedToPlay($game, 2));

        // This should be wrong
        $game->status = \App\Models\Game::STATUS_TURN_OF_PLAYER_2;
        $game->save();

        $this->assertFalse(\App\Models\User::isAllowedToPlay($game, 2));

        // This should be wrong
        $game->player1_id = 3;
        $game->save();

        $this->assertFalse(\App\Models\User::isAllowedToPlay($game, 2));

        // Reset the values
        $game->player1_id = 2;
        $game->save();
    }

    public function testRemoveJoker()
    {
        $game = \App\Models\Game::find(6);
        $user = \App\Models\User::find(2);

        $this->assertTrue($game->player1_joker_available == 1);
        $this->assertTrue($user->removeJoker($game, 2));
        $this->assertTrue($game->player1_joker_available == 0);

        $game->player1_joker_available = 1;
        $game->save();
    }
}
