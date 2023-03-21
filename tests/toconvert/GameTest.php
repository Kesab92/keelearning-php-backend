<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class GameTest extends TestCase
{
    public function testGetActiveGame()
    {
        $game = \App\Models\Game::active()->orderByRaw('RAND()')->take(1)->first();
//        $this->assertNotEquals(\App\Models\Game::STATUS_FINISHED, $game->status);
    }

    public function testFinishPlayerRound()
    {
        $game = \App\Models\Game::find(1);
        $game->status = \App\Models\Game::STATUS_TURN_OF_PLAYER_1;
        $game->save();

        $gameAnswers = \App\Models\GameQuestionAnswer::all();
        foreach ($gameAnswers as $gameQuestionAnswer) {
            $gameQuestionAnswer->delete();
        }

        // Answer the game for the first time
        $gameQuestionAnswer = new \App\Models\GameQuestionAnswer();
        $gameQuestionAnswer->game_question_id = 1;
        $gameQuestionAnswer->user_id = 2;
        $gameQuestionAnswer->question_answer_id = 506;
        $gameQuestionAnswer->save();

        $round1 = \App\Models\GameRound::find(1);
        $assertion1 = $round1->isFinishedFor(2);
        $this->assertFalse($assertion1);

        // Second time
        $gameQuestionAnswer1 = new \App\Models\GameQuestionAnswer();
        $gameQuestionAnswer1->game_question_id = 2;
        $gameQuestionAnswer1->user_id = 2;
        $gameQuestionAnswer1->question_answer_id = 538;
        $gameQuestionAnswer1->save();

        $assertion2 = $round1->isFinishedFor(2);
        $this->assertFalse($assertion2);

        // Third time
        $gameQuestionAnswer2 = new \App\Models\GameQuestionAnswer();
        $gameQuestionAnswer2->game_question_id = 3;
        $gameQuestionAnswer2->user_id = 2;
        $gameQuestionAnswer2->question_answer_id = 513;
        $gameQuestionAnswer2->save();

        $assertion3 = $round1->isFinishedFor(2);
        $this->assertTrue($assertion3);

        // Check if the status change happens in the right way
        $this->assertEquals(\App\Models\Game::STATUS_TURN_OF_PLAYER_1, $game->status);

        $game->finishPlayerRound();

        $this->assertEquals(\App\Models\Game::STATUS_TURN_OF_PLAYER_2, $game->status);

        $gameQuestionAnswer->delete();
        $gameQuestionAnswer1->delete();
        $gameQuestionAnswer2->delete();
    }

    public function testPlayRound()
    {
        $round1 = \App\Models\GameRound::find(1);
        $game = \App\Models\Game::find(1);
        $game->status = \App\Models\Game::STATUS_TURN_OF_PLAYER_2;
        $game->save();

        //Play the game with id1 for one round
        //Player 2: id 1

        $gameQuestionAnswer1 = new \App\Models\GameQuestionAnswer();
        $gameQuestionAnswer1->game_question_id = 1;
        $gameQuestionAnswer1->user_id = 1;
        $gameQuestionAnswer1->question_answer_id = 505;
        $gameQuestionAnswer1->save();

        $this->assertFalse($round1->isFinishedFor(1));

        $gameQuestionAnswer2 = new \App\Models\GameQuestionAnswer();
        $gameQuestionAnswer2->game_question_id = 2;
        $gameQuestionAnswer2->user_id = 1;
        $gameQuestionAnswer2->question_answer_id = 537;
        $gameQuestionAnswer2->save();

        $this->assertFalse($round1->isFinishedFor(1));

        $gameQuestionAnswer3 = new \App\Models\GameQuestionAnswer();
        $gameQuestionAnswer3->game_question_id = 3;
        $gameQuestionAnswer3->user_id = 1;
        $gameQuestionAnswer3->question_answer_id = 513;
        $gameQuestionAnswer3->save();

        $this->assertTrue($round1->isFinishedFor(1));

        $game->finishPlayerRound();

        $this->assertEquals(\App\Models\Game::STATUS_TURN_OF_PLAYER_1, $game->status);

        //Player 1: id 2

        $gameQuestionAnswer11 = new \App\Models\GameQuestionAnswer();
        $gameQuestionAnswer11->game_question_id = 1;
        $gameQuestionAnswer11->user_id = 2;
        $gameQuestionAnswer11->question_answer_id = 505;
        $gameQuestionAnswer11->save();

        $this->assertFalse($round1->isFinishedFor(2));

        $gameQuestionAnswer12 = new \App\Models\GameQuestionAnswer();
        $gameQuestionAnswer12->game_question_id = 2;
        $gameQuestionAnswer12->user_id = 2;
        $gameQuestionAnswer12->question_answer_id = 537;
        $gameQuestionAnswer12->save();

        $this->assertFalse($round1->isFinishedFor(2));

        $gameQuestionAnswer13 = new \App\Models\GameQuestionAnswer();
        $gameQuestionAnswer13->game_question_id = 3;
        $gameQuestionAnswer13->user_id = 2;
        $gameQuestionAnswer13->question_answer_id = 513;
        $gameQuestionAnswer13->save();

        $this->assertTrue($round1->isFinishedFor(2));

        $game->finishPlayerRound();

        $this->assertTrue($round1->isFinishedFor(1) && $round1->isFinishedFor(2));

        $gameQuestionAnswer11->delete();
        $gameQuestionAnswer12->delete();
        $gameQuestionAnswer13->delete();
    }
}
