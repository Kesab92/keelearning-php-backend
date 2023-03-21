<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class GameEngineTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testGameCreation()
    {
        // Create a game with rounds and questions
        $gameEngine = new \App\Services\GameEngine();
        $gameId = $gameEngine->spawnGame(1, 2, 1);
        $game = \App\Models\Game::find($gameId);

        $this->assertDatabaseHas('games', ['id' => $gameId]);
        $this->assertDatabaseHas('game_rounds', ['game_id' => $gameId]);
        // Plyer 2 should start game
        $this->assertEquals(\App\Models\Game::STATUS_TURN_OF_PLAYER_1, $game->status);
        // Both players should be duelists
        $this->assertEquals(true, $game->isDuelist(2));
        $this->assertEquals(true, $game->isDuelist(1));

        // GameQuestions and GameRounds
        $gameRounds = $game->gameRounds();
        foreach ($gameRounds as $gameRound) {
            $this->assertDatabaseHas('game_questions', ['game_round_id' => $gameRound->id]);
        }
    }

    public function testDetermineWinnerOfGame()
    {

        // Create a game with answers
        $game = \App\Models\Game::find(4);
        $gameEngine = new \App\Services\GameEngine();

        $gameQuestionAnswer2Player_1 = new \App\Models\GameQuestionAnswer();
        $gameQuestionAnswer2Player_1->game_question_id = 46;
        $gameQuestionAnswer2Player_1->user_id = 4;
        $gameQuestionAnswer2Player_1->question_answer_id = 534;
        $gameQuestionAnswer2Player_1->save();

        $game->status = \App\Models\Game::STATUS_TURN_OF_PLAYER_1;
        $game->save();

        $gameQuestionAnswer1Player_1 = new \App\Models\GameQuestionAnswer();
        $gameQuestionAnswer1Player_1->game_question_id = 46;
        $gameQuestionAnswer1Player_1->user_id = 2;
        $gameQuestionAnswer1Player_1->question_answer_id = 533;
        $gameQuestionAnswer1Player_1->save();

        $game->status = \App\Models\Game::STATUS_TURN_OF_PLAYER_2;
        $game->save();

        // Determine winner of game and check if this is user with id 3
        $gameResults = $gameEngine->determineWinnerOfGame($game);

        $this->assertTrue($gameResults['state'] == \App\Services\GameEngine::PLAYER2_WON);
        $this->assertTrue($gameResults['winnerId'] == 4);

        $gameQuestionAnswer1Player_1->delete();
        $gameQuestionAnswer2Player_1->delete();
    }

    public function testQuestionsAreDistinct()
    {
        for ($i = 0; $i < 10; $i++) {
            $gameEngine = new \App\Services\GameEngine();
            $gameId = $gameEngine->spawnGame(2, 4, 1);

            $game = \App\Models\Game::find($gameId);

            $questionIds = [];
            $counter = 0;

            // Go through the whole game to get the ids of the questions
            foreach ($game->gameRounds as $gameRound) {
                foreach ($gameRound->gameQuestions as $gameQuestion) {
                    $counter++;
                    $questionIds[] = $gameQuestion->question_id;
                }
            }

            // Remove duplicate entries and assume, that no entry had to be removed
            $questionIds = array_unique($questionIds);
            $this->assertTrue($counter == count($questionIds));
        }
    }
}
