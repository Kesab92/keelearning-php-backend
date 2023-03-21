<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class DoorKeeperTest extends TestCase
{
    public function testUserIsAllowedToPlay()
    {

        // True
        /** @var \App\Models\Game $game */
        $game = \App\Models\Game::find(1);
        $game->status = \App\Models\Game::STATUS_TURN_OF_PLAYER_1;
        $game->save();

        $this->assertTrue(\App\Services\DoorKeeper::userIsAllowedToPlay($game, 2));

        // APIError: not my turn
        $game->status = \App\Models\Game::STATUS_TURN_OF_PLAYER_2;
        $game->save();

        $isAPIError = is_a(\App\Services\DoorKeeper::userIsAllowedToPlay($game, 2), \App\Http\APIError::class);
        $this->assertTrue($isAPIError);

        // APIError: game finished
        $game->status = \App\Models\Game::STATUS_FINISHED;
        $game->save();

        $isAPIError = is_a(\App\Services\DoorKeeper::userIsAllowedToPlay($game, 2), \App\Http\APIError::class);
        $this->assertTrue($isAPIError);

        // Reset values
        $game->status = \App\Models\Game::STATUS_TURN_OF_PLAYER_2;
        $game->save();
    }

    public function testUserIsAllowedToUseJoker()
    {
        $game = \App\Models\Game::find(4);
        $game->player1_joker_available = 1;
        $game->save();

        $decision1 = \App\Services\DoorKeeper::userIsAllowedToUseJoker($game, 2);
        $this->assertTrue($decision1);

        $game->player1_joker_available = 0;
        $game->save();

        $decision2 = \App\Services\DoorKeeper::userIsAllowedToUseJoker($game, 2);
        $this->assertFalse($decision2);
    }

    public function testGameStatusNotFittingGetGame()
    {
        $this->setAPIUser(2);
        // Create a game
        $response1 = $this->post('/api/v1/games', [
            'opponent_id' => 4,
        ])->response;

        $results1 = json_decode($response1->getContent(), true);
        $gameId = $results1['game_id'];

        var_dump('gameId '.$gameId);

        $this->assertDatabaseHas('games', [
            'id' => $gameId,
            'player1_id' => 2,
            'player2_id' => 4,
        ]);

        // Answer the first two questions and only fetch the third one
        for ($i = 0; $i < 3; $i++) {
            // Get the id of the current gameQuestion
            /** @var \App\Models\Game $game */
            $game = \App\Models\Game::find($gameId);

            // Get question
            $response2 = $this->get('/api/v1/games/'.$gameId.'/question')->response;

            $this->assertTrue($response2->isOk());

            $this->assertDatabaseHas('game_question_answers', [
                'user_id' => 2,
                'question_answer_id' => null,
            ]);

            // Give answers for the first two questions
            if ($i != 2) {
                $results2 = json_decode($response2->getContent(), true);
                $answers = $results2['answers'];
                $randomAnswerId = $answers[array_rand($answers)]['id'];

                $response3 = $this->post('/api/v1/games/'.$gameId.'/question', [
                    'question_answer_id' => $randomAnswerId,
                ])->response;

                $this->assertTrue($response3->isOk());

                $this->assertDatabaseHas('game_question_answers', [
                    'user_id' => 2,
                    'question_answer_id' => $randomAnswerId,
                ]);

                $decision1 = \App\Services\DoorKeeper::gameStatusIsFitting($gameId, 2);
                $this->assertTrue($decision1);

                // There are no problems for the second user
                $decision11 = \App\Services\DoorKeeper::gameStatusIsFitting($gameId, 4);
                $this->assertTrue($decision11);
            }

            // The last gamequestion was not answered properly
            if ($i == 2) {
                $decision2 = \App\Services\DoorKeeper::gameStatusIsFitting($gameId, 2);
                $this->assertTrue(is_a($decision2, \App\Models\GameQuestionAnswer::class));

                // There are no problems for the second user
                $decision21 = \App\Services\DoorKeeper::gameStatusIsFitting($gameId, 4);
                $this->assertTrue($decision21);

                $gameStatusOld = $game->status;
                /**
                 * Involve the GamesController.
                 */
                $response4 = $this->get('/api/v1/games/'.$gameId.'/question')->response;

                // An APIError should be returned
                $this->assertFalse($response4->isOk());
                $results4 = json_decode($response4->getContent(), true);
                $this->assertTrue($results4['message'] != '');

                // Assert, that the status has changed
                /** @var \App\Models\Game $game */
                $game = \App\Models\Game::find($gameId);
                $this->assertTrue($game->status != $gameStatusOld);
            }

//            var_dump('####################');
//            $this->setAPIUser(2);
//            $response10 = $this->post('/api/v1/games/' . $gameId)->response;
//            $results10 = json_decode($response10->getContent(), true);
//            var_dump($results10);
//            $this->assertTrue($response10->isOk());
//
//            $this->setAPIUser(2);
//            $response20 = $this->post('/api/v1/games/' . $gameId)->response;
//            $results20 = json_decode($response20->getContent(), true);
//            var_dump($results20);
//            var_dump('####################');
//            $this->assertTrue($response20->isOk());
//
//            $this->assertTrue($results10['status'] == $results20['status']);
        }
    }
}
