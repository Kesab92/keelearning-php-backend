<?php

namespace Tests;

use App\Models\Game;
use App\Services\Terminator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class GameAPITest extends TestCase
{
    private $dayBeforeYesterDay;
    private $lastWeek;

    public function __construct()
    {
        $now = Carbon::now();
        $this->dayBeforeYesterDay = $now->subDays(2);
        $this->lastWeek = $now->subWeek();
    }

    public function testPositiveGetGame()
    {
        $this->setAPIUser(2);
        $response = $this->json('GET', '/api/v1/games/10');

        $response->assertStatus(200)
                 ->assertJson([
                     'id'         => 10,
                     'player1_id' => 2,
                     'player2_id' => 4,
                     'player1'    => 'Fabiano',
                     'player2'    => 'Tim Tester',
                     'status'     => \App\Models\Game::STATUS_TURN_OF_PLAYER_1,
                     2            => null,
                     4            => null,
                 ]);
    }

    public function testNegativGetGame()
    {
        $this->setAPIUser(4);
        // The player is not involved in this game
        $badResponse = $this->get('/api/v1/games/9')->seeJson([
            'id' => 9,
        ], true);
    }

    public function testLoginUser()
    {
        $this->post('/api/v1/login', [
            'email' => 'f.henkel@sopamo.de',
            'password' => 'yolo',
            'appId' => 1,
        ])->seeJson([
            'id' => 2,
            'name' => 'Fabiano',
        ]);
    }

//    public function testSearchForUser() {
//
//        $this->get('/api/v1/users/search?q=Paul', [
//            'Authorization' => $this->authPlayerID2
//        ])
//            ->seeJson([
//                'id' => 1,
//                'username' => 'Paul'
//            ]);
//
//        $this->get('/api/v1/users/search?q=', [
//            'Authorization' => $this->authPlayerID2
//        ])
//            ->seeJson([
//                'id' => 2,
//                'username' => 'Fabiano'
//            ], true);
//
//    }

    public function testGetActiveGames()
    {
        $this->setAPIUser(2);
        $this->get('/api/v1/games/active')->seeJson([
            'status' => 'finished',
        ], true);
    }

    public function testGetRecentGames()
    {

        /** @var \App\Models\Game $game */
        $game = \App\Models\Game::find(4);

        // Create a game with answers
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

        $game->status = \App\Models\Game::STATUS_FINISHED;
        $game->save();

        $this->setAPIUser(2);

        $this->get('/api/v1/games/recent')->seeJson([
            'status' => 'opponentsTurn',
        ], true)->seeJson([
            'status' => 'myTurn',
        ], true)->seeJson([
            'winner' => 4,
        ]);

        $gameQuestionAnswer2Player_1->delete();
        $gameQuestionAnswer1Player_1->delete();

        $game->status = \App\Models\Game::STATUS_TURN_OF_PLAYER_2;
        $game->save();
    }

    public function testCreateGame()
    {
        $this->setAPIUser(2);
        $response = $this->post('/api/v1/games', [
            'opponent_id' => 1,
        ])->response;

        $this->assertTrue($response->isOk());
        $results = json_decode($response->getContent(), true);

        $gameId = $results['game_id'];

        /** @var \App\Models\Game $game */
        $game = \App\Models\Game::find($gameId);

        $this->assertDatabaseHas('games', ['id' => $gameId]);
        $this->assertDatabaseHas('game_rounds', ['game_id' => $gameId]);
        // Plyer 2 should start game
        $this->assertEquals(\App\Models\Game::STATUS_TURN_OF_PLAYER_1, $game->status);
        // Both players should be duelists
        $this->assertEquals(true, $game->isDuelist(2));
        $this->assertEquals(true, $game->isDuelist(1));

        // GameQuestions and GameRounds
        $gameRounds = $game->gameRounds;
        foreach ($gameRounds as $gameRound) {
            $this->assertDatabaseHas('game_questions', ['game_round_id' => $gameRound->id]);
        }

        $this->setAPIUser(2);

        // Create a game with a random player
        $response1 = $this->post('/api/v1/games', [
            'opponent_id' => null,
        ])->response;

        $this->assertTrue($response1->isOk());
        $results1 = json_decode($response1->getContent(), true);

        $gameId1 = $results1['game_id'];

        /** @var \App\Models\Game $game1 */
        $game1 = \App\Models\Game::find($gameId1);

        $this->assertTrue($game1->player1_id != $game1->player2_id);
        $this->assertTrue($game1->player2_id != null && $game1->player2_id != '');
    }

    public function testGetNextQuestion()
    {

        /** @var \App\Models\Game $game */
        $game = \App\Models\Game::find(2);

        // Allow player1 with id 2 to make his turn
        $game->status = \App\Models\Game::STATUS_TURN_OF_PLAYER_1;
        $game->save();

        $gameRound = $game->gameRounds->first();
        $gameQuestion = $gameRound->gameQuestions->first();
        $question = $gameQuestion->question;
        $category = $gameRound->category;

        $this->setAPIUser(2);

        $response = $this->get('/api/v1/games/2/question');

        $response->seeJson([
            'id' => $question->id,
            'category' => $category->name,
            'title' => $question->title,
        ]);

        $response = $response->response;

        // Check if there are as many answers as there should be
        $results = json_decode($response->getContent(), true);
        $this->assertEquals($game->app->answers_per_question, count($results['answers']));

        $this->assertDatabaseHas('game_question_answers', [
            'user_id' => 2,
            'game_question_id' => $gameQuestion->id,
            'question_answer_id' => null,
        ]);

        // Reset
        $game->status = \App\Models\Game::STATUS_TURN_OF_PLAYER_2;
        $game->save();
    }

    public function testAnswerQuestion()
    {

        /** @var \App\Models\Game $game */
        $game = \App\Models\Game::find(2);
        $game->status = \App\Models\Game::STATUS_TURN_OF_PLAYER_1;
        $game->save();

        $this->setAPIUser(2);

        $response = $this->get('/api/v1/games/2/question')->response;

        // Check if there are as many answers as there should be
        $results = json_decode($response->getContent(), true);

        $answerTitlesAndIds = $results['answers'];
        $answerId = $answerTitlesAndIds[array_rand($answerTitlesAndIds)]['id'];

        $this->assertTrue($game->status == \App\Models\Game::STATUS_TURN_OF_PLAYER_1);

        $this->setAPIUser(2);

        $response1 = $this->post('/api/v1/games/2/question', [
            'question_answer_id' => $answerId,
        ]);

        $this->assertDatabaseHas('game_question_answers', [
            'user_id' => 2,
            'question_answer_id' => $answerId,
        ]);

        $game->status = \App\Models\Game::STATUS_TURN_OF_PLAYER_2;
        $game->save();
    }

    public function testNotAnsweredQuestions()
    {

        /** @var \App\Models\Game $game */
        $game = \App\Models\Game::find(6);
        $game->status = \App\Models\Game::STATUS_TURN_OF_PLAYER_1;
        $game->save();

        $this->setAPIUser(2);

        // Get question
        $response = $this->get('/api/v1/games/6/question');

        $this->assertTrue($response->response->isOk());

        $gameQuestionAnswer = \App\Models\GameQuestionAnswer::ofUser(2)->orderBy('id', 'DESC')->first();
        $this->assertTrue($gameQuestionAnswer->question_answer_id == null);

        $gameQuestionAnswer->created_at = \Carbon\Carbon::now()->subSeconds(50);
        $gameQuestionAnswer->save();

        $this->setAPIUser(2);

        // Answer question
        $this->post('/api/v1/games/6/question', [
            'question_answer_id' => 214,
        ]);

        $this->setAPIUser(2);

        $this->get('/api/v1/games/6');

        $game->status = \App\Models\Game::STATUS_TURN_OF_PLAYER_2;
        $game->save();
        $gameQuestionAnswer->save();
    }

    public function testUseJoker()
    {
        $game = \App\Models\Game::find(8);
        $this->assertTrue($game->player1_joker_available == 1);

        $this->setAPIUser(2);

        $response1 = $this->post('/api/v1/games/8/joker', [
            'answer_ids' => [111, 109, 112],
        ]);

        $response1 = $response1->response;
        $results = json_decode($response1->getContent(), true);
        $this->assertEquals(1, count($results['wrong']));

        foreach ($results['wrong'] as $answerId) {
            $questionAnswer = \App\Models\QuestionAnswer::find($answerId);
            $this->assertTrue($questionAnswer->correct == 0);
        }

        $game->player1_joker_available = 1;
        $game->save();
    }

    public function testGetUser()
    {
        $this->setAPIUser(2);

        $response = $this->get('/api/v1/users/2');

        $response->seeJson([
            'id' => 2,
            'username' => 'Fabiano',
            'email' => 'f.henkel@sopamo.de',
        ]);
    }

    public function testUploadAvatar()
    {
        $this->setAPIUser(2);

        $response = $this->post('/api/v1/profile/avatar', [
            'image' => 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/2wBDAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/wAARCALGAj4DASIAAhEBAxEB/8QAHwAAAQUBAQEBAQEAAAAAAAAAAAECAwQFBgcICQoL/8QAtRAAAgEDAwIEAwUFBAQAAAF9AQIDAAQRBRIhMUEGE1FhByJxFDKBkaEII0KxwRVS0fAkM2JyggkKFhcYGRolJicoKSo0NTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uHi4+Tl5ufo6erx8vP09fb3+Pn6/8QAHwEAAwEBAQEBAQEBAQAAAAAAAAECAwQFBgcICQoL/8QAtREAAgECBAQDBAcFBAQAAQJ3AAECAxEEBSExBhJBUQdhcRMiMoEIFEKRobHBCSMzUvAVYnLRChYkNOEl8RcYGRomJygpKjU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6goOEhYaHiImKkpOUlZaXmJmaoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4uPk5ebn6Onq8vP09fb3+Pn6/9oADAMBAAIRAxEAPwD+/YAYHA6DsKXA9B+QoXoPoP5UtACYHoPyFGB6D8hS0UAJgeg/IUYHoPyFLRQAmB6D8hRgeg/IUtFACYHoPyFGB6D8hS0UAJgeg/IUYHoPyFLRQAmB6D8hRgeg/IUtFACYHoPyFGB6D8hS0UAJgeg/IUYHoPyFLRQAmB6D8hRgeg/IUtFACYHoPyFGB6D8hS0UAJgeg/IUYHoPyFLRQAmB6D8hRgeg/IUtFACYHoPyFGB6D8hS0UAJgeg/IUYHoPyFLRQAmB6D8hRgeg/IUtFACYHoPyFGB6D8hS0UAJgeg/IUYHoPyFLRQAmB6D8hRgeg/IUtFACYHoPyFGB6D8hS0UAJgeg/IUYHoPyFLRQAmB6D8hRgeg/IUtFACYHoPyFGB6D8hS0UAJgeg/IUYHoPyFLRQAmB6D8hRgeg/IUtFACYHoPyFGB6D8hS0UAJgeg/IUYHoPyFLRQAmB6D8hRgeg/IUtFACYHoPyFGB6D8hS0UAJgeg/IUYHoPyFLRQAmB6D8hRgeg/IUtFACYHoPyFGB6D8hS0UAJgeg/IUYHoPyFLRQAmB6D8hRgeg/IUtFACYHoPyFGB6D8hS0UAJgeg/IVG4AxgAdeg+lS1HJ2/H+lAD16D6D+VLSL0H0H8qWgAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKjk7fj/SpKjk7fj/SgB69B9B/KlpF6D6D+VLQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFRydvx/pUlRydvx/pQA9eg+g/lS0i9B9B/KloAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACo5O34/0qSo5O34/0oAevQfQfypaReg+g/lS0AFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABUcnb8f6VJUcnb8f6UAPXoPoP5UtIvQfQfypaACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACii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eJ0U2tGmkrJPez389dND9x/2WvjHB4x0K2sLidXmWJFALDOduO5HvwQenpX2mP8AH8u36V/Pf+yJ46vfCniu3tb65K273KIoZiABvAHU46fh057V+++iatb6rplpeQOsizQoxKkHBK5/P198854r+tvDTiKWfZHTdWpGVaglFxv79rK7avstNtNWz+WvEfh15FnU5QpuGGrNuM7JQcpNWWnVt79bGzRTPMX3/T/GmiZdxXH+f8+/0zX6KqkHHnUlyt2T1tfY/PrPXTZXfkiWikByM0tWndXWz1QgooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAqOTt+P8ASpKjk7fj/SgB69B9B/KlpF6D6D+VLQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQBHIMgegzn8cVAyowXnBznj6/r+v4VaIyCPWqzAKCD2zg9/XIxz6dKhx1k7tXa266Jf8D8fUSu9vstva9rx2b2+RBezfZbSe44GyJyCfYH8uePoTX4m/tdeI21nxHPF53EMhXGeDgkcDPGeOOOnOa/ZPxTdtbeHtQnYY228mCfo3f39skZ7nmv59/2htf8AtHizU2eQj9/IeSMYDHHfnp649O1fh/jbmLw+VYfL1U9nLEOLjdq14tPVdfW263P2jwSy5YvPKuNaahh4TXO1fSdlo/LtfTRrqeHDU/skhRWyTnv36fyBzW/FqdzDD5y8qMHj8e/1ycgjpgY4rw+71lX1BQsv8fQkYPzcd+mOw/PoK7mfxNb29hHFIy5YAcYHX6npzzxj3r+UaeOqQxC6uLSctbNPlV79dN/0P60nltOph9tVsnqnorXW+qe9n13R6INdbUrYoWKyL0PIPHvwf5duvSub1e6ItxGGPmdznr0649v844rzXUfFX9mFJ7c7o2wWAPbPPTOM5OMH9av6b4ntdYRnaQFyMbc8rn6dPQdzXvQzBVIVaFWPN7SKtUa91t2966tqtrN62sfMYvBqhUoulBN0patesdF/L53T2udHpXiuTw/eWVxbyGOaOdXLKcN8pBycf454x05r92v2TPiqvi/wzZ2lxdK8yQxrh3BbIUdjk5Ocex788/zqeKpTAEnifJ37sDHAzgnGfy69s98f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',
        ])->seeJson([
            'success' => 1,
        ]);
    }

    public function testSuggestQuestion()
    {
        $this->setAPIUser(2);

        // Successful creation of suggestedquestions
        $response1 = $this->post('/api/v1/questions/suggest', [
            'title' => 'title',
            'correct' => 'correct',
            'category' => 1,
            'wrong' => [
                'wrong1',
                'wrong2',
                'wrong3',
            ],
        ])->seeJson([
            'success' => 1,
        ]);

        $this->assertDatabaseHas('suggested_questions', [
            'title' => 'title',
            'category_id' => 1,
        ]);

        $this->assertDatabaseHas('suggested_question_answers', [
            'suggested_question_id' => 1,
            'content' => 'correct',
            'correct' => 1,
        ]);

        $this->assertDatabaseHas('suggested_question_answers', [
            'suggested_question_id' => 1,
            'content' => 'wrong1',
            'correct' => 0,
        ]);

        $app = user()->app;
        $answersCount = $app->answers_per_question;

        $this->setAPIUser(2);

        // Errors during creation
        $response2 = $this->post('/api/v1/questions/suggest', [
            'title' => '',
            'correct' => 'correct',
            'category' => 1,
            'wrong' => [
                'wrong1',
                'wrong2',
                'wrong3',
            ],
        ])->seeJson([
            'message' => 'Bitte überprüfe Deine Eingaben. Kein Feld darf leer sein!',
        ]);

        $this->setAPIUser(2);

        $response3 = $this->post('/api/v1/questions/suggest', [
            'title' => 'title',
            'correct' => 'correct',
            'category' => 1,
            'wrong' => [
                'wrong1',
                'wrong2',
            ],
        ])->seeJson([
            'message' => 'Du musst mindestens '.$answersCount.' Antworten geben!',
        ]);
    }

    public function testInvalidateGivenAnswers()
    {
        $this->setAPIUser(2);
        // Create a game: Player2(p1) vs Player4(p2)
        $response1 = $this->post('/api/v1/games', [
            'opponent_id' => 4,
        ])->response;

        $results1 = json_decode($response1->getContent(), true);
        $gameId = $results1['game_id'];

        $this->assertDatabaseHas('games', [
            'id' => $gameId,
            'player1_id' => 2,
            'player2_id' => 4,
        ]);

        // Player2 answers questions
        for ($i = 0; $i < 3; $i++) {
            $this->setAPIUser(2);

            // Get question
            $response2 = $this->get('/api/v1/games/'.$gameId.'/question')->response;

            $this->assertTrue($response2->isOk());

            $results2 = json_decode($response2->getContent(), true);
            $answers = $results2['answers'];

            $this->assertTrue(count($answers) == 4);

            $randomAnswerId = $answers[array_rand($answers)]['id'];

            $this->assertDatabaseHas('game_question_answers', [
                'user_id' => 2,
                'question_answer_id' => null,
            ]);

            $this->setAPIUser(2);
            // Give the answer
            $response3 = $this->post('/api/v1/games/'.$gameId.'/question', [
                'question_answer_id' => $randomAnswerId,
            ])->response;

            $this->assertTrue($response3->isOk());
        }

        // We immediately created the game, so this should be false
        $this->assertFalse(Terminator::isGameTooOld($gameId));
        /** @var \App\Models\Game $game */
        $game = \App\Models\Game::find($gameId);
        $game->created_at = $this->lastWeek;
        $game->save();

        $this->assertTrue(Terminator::isGameTooOld($gameId));

        /** @var \App\Services\GameEngine $gameEngine */
        $gameEngine = new \App\Services\GameEngine();
        $gameEngine->finishWholeGame($gameId, null);

        // Completely all answers of this game should now point to no answer
        /** @var \App\Models\GameRound $round */
        foreach ($game->gameRounds() as $round) {
            $gameQuestionAnswers = $round->hasManyThrough(\App\Models\GameQuestionAnswer::class, \App\Models\GameRound::class);

            /** @var \App\Models\GameQuestionAnswer $gameQuestionAnswer */
            foreach ($gameQuestionAnswers as $gameQuestionAnswer) {
                $this->assertTrue($gameQuestionAnswer->question_answer_id == null);
            }
        }
    }

    /**
     * Create game.
     */
    public function testPart1()
    {
        $this->setAPIUser(2);

        // Create a game: Player2(p1) vs Player4(p2)
        $response1 = $this->post('/api/v1/games', [
            'opponent_id' => 4,
        ])->response;

        $results1 = json_decode($response1->getContent(), true);
        $gameId = $results1['game_id'];

        $this->assertDatabaseHas('games', [
            'id' => $gameId,
            'player1_id' => 2,
            'player2_id' => 4,
        ]);

        // Test gameIsTooOld
        $this->assertFalse(Terminator::isGameTooOld($gameId));

        // Manipulate the time of creation
        /** @var \App\Models\Game $game */
        $game = \App\Models\Game::find($gameId);
        $game->created_at = $this->lastWeek;
        $game->save();

        $this->assertTrue(Terminator::isGameTooOld($gameId));
        // Test if the challenge got accepted
        $this->assertFalse($game->isChallengeAccepted());

        return $gameId;
    }

    /**
     * Round 1.
     *
     * @depends testPart1
     */
    public function testPart2($gameId)
    {
        $scorePlayer1 = 0;
        $scorePlayer2 = 0;

        // Check if the round index is fetched correctly
        $game = \App\Models\Game::find($gameId);
        $round = $game->getCurrentRound();
        $this->assertTrue($game->getCurrentRoundIndex() == 1);

        /*
         * Player2 begins answering questions
         */
        for ($i = 0; $i < 3; $i++) {
            if ($i == 2) {
                Log::debug('??????????????????????');
            }

            $this->setAPIUser(2);

            // Get question
            $response2 = $this->get('/api/v1/games/'.$gameId.'/question')->response;

            if ($i == 2) {
                Log::debug('This should be before the invitation mail');
            }

            $results2 = json_decode($response2->getContent(), true);
            $this->assertTrue($response2->isOk());
            $answers = $results2['answers'];

            $this->assertTrue(count($answers) == 4);

            $randomAnswerId = $answers[array_rand($answers)]['id'];

            $this->assertDatabaseHas('game_question_answers', [
                'user_id' => 2,
                'question_answer_id' => null,
            ]);

            // Manipulate the creation time of the first answer for the terminator game is too old check
            if ($i == 0) {
                $gameQuestionAnswer = \App\Models\GameQuestionAnswer::ofUser(2)
                    ->ofGame($gameId)
                    ->orderBy('id', 'DESC')
                    ->first();

                $gameQuestionAnswer->created_at = $this->dayBeforeYesterDay;
                $gameQuestionAnswer->save();
            }
            // As the terminator should finish the game, set the already created empty gameQuestion 50 seconds to the past
            // and finish it artificially
            if ($i == 2) {
                $gameQuestionAnswer = \App\Models\GameQuestionAnswer::ofUser(2)
                    ->ofGame($gameId)
                    ->orderBy('id', 'DESC')
                    ->first();

                $gameQuestionAnswer->created_at = $this->dayBeforeYesterDay;
                $gameQuestionAnswer->save();

                // Because the creation time of the first answer was manipulated
                $this->assertTrue(Terminator::isGameTooOld($gameId));
                $game = Game::find($gameId);
                $this->assertTrue(Terminator::seekAndFinishRound($game));

                Log::debug('This should be after the invitation mail');

                /** @var Game $game */
                $game = \App\Models\Game::find($gameId);
                $this->assertTrue($game->status == \App\Models\Game::STATUS_TURN_OF_PLAYER_2);

                /** @var \App\Models\GameQuestionAnswer $gameQuestionAnswer */
                $gameQuestionAnswer = \App\Models\GameQuestionAnswer::ofUser(2)
                    ->ofGame($gameId)
                    ->orderBy('id', 'DESC')
                    ->first();

                $this->assertTrue($gameQuestionAnswer->question_answer_id == -1);
            }

            // Give the answer
            // If this is not the first answer, that was already given by the terminator ;)
            if ($i != 2) {
                $this->setAPIUser(2);

                $response3 = $this->post('/api/v1/games/'.$gameId.'/question', [
                    'question_answer_id' => $randomAnswerId,
                ])->response;

                $this->assertTrue($response3->isOk());

                $results3 = json_decode($response3->getContent(), true);
                $correctQuestionAnswerId = $results3['correct_answer_id'];

                // The given and the correct answer are equal. Give the player a point
                if ($randomAnswerId == $correctQuestionAnswerId) {
                    $scorePlayer1++;
                }
            }
        }

        // Count all gameQuestionAnswers given for this round
        $gameQuestionAnswers = \App\Models\GameQuestionAnswer::ofRound($round->id);
        $this->assertTrue($gameQuestionAnswers->count() == 3);

        // Test if the challenge got accepted
        $this->assertFalse($game->isChallengeAccepted());

        return [
            'gameId' => $gameId,
            'scorePlayer1' => $scorePlayer1,
            'scorePlayer2' => $scorePlayer2,
        ];
    }

    /**
     * @depends testPart2
     */
    public function testPart3($data)
    {
        $gameId = $data['gameId'];
        $game = \App\Models\Game::find($gameId);
        $round = $game->getCurrentRound();
        $scorePlayer1 = $data['scorePlayer1'];
        $scorePlayer2 = $data['scorePlayer2'];

        $this->assertTrue(Terminator::isGameTooOld($gameId));

        /*
         * Player4 answers questions
         */
        for ($i = 0; $i < 3; $i++) {
            $this->setAPIUser(4);

            // Get question
            $response2 = $this->get('/api/v1/games/'.$gameId.'/question')->response;

            $this->assertTrue($response2->isOk());

            $results2 = json_decode($response2->getContent(), true);
            $answers = $results2['answers'];

            $this->assertTrue(count($answers) == 4);

            $randomAnswerId = $answers[array_rand($answers)]['id'];

            $this->setAPIUser(4);

            if ($i == 2) {
                Log::debug('This should be before the round mail');
            }

            // Give the answer
            $response3 = $this->post('/api/v1/games/'.$gameId.'/question', [
                'question_answer_id' => $randomAnswerId,
            ])->response;

            if ($i == 2) {
                Log::debug('This should be after the round mail');
            }

            $this->assertTrue($response3->isOk());

            $results3 = json_decode($response3->getContent(), true);
            $correctQuestionAnswerId = $results3['correct_answer_id'];

            // The given and the correct answer are equal. Give the player a point
            if ($randomAnswerId == $correctQuestionAnswerId) {
                $scorePlayer2++;
            }
        }

        // Count all gameQuestionAnswers given for this round
        $gameQuestionAnswers = \App\Models\GameQuestionAnswer::ofRound($round->id);
        $this->assertTrue($gameQuestionAnswers->count() == 6);

        // Test if the challenge got accepted
        $this->assertTrue($game->isChallengeAccepted());

        return [
            'gameId' => $gameId,
            'scorePlayer1' => $scorePlayer1,
            'scorePlayer2' => $scorePlayer2,
        ];
    }

    /**
     * Round 2.
     *
     * @depends testPart3
     */
    public function testPart4($data)
    {
        $gameId = $data['gameId'];
        $scorePlayer1 = $data['scorePlayer1'];
        $scorePlayer2 = $data['scorePlayer2'];

        /** @var Game $game */
        $game = \App\Models\Game::find($gameId);
        $this->assertTrue($game->getCurrentRoundIndex() == 2);

        /*
         * Player2 begins answering questions
         */
        for ($i = 0; $i < 3; $i++) {
            $this->setAPIUser(2);

            // Get question
            $response2 = $this->get('/api/v1/games/'.$gameId.'/question')->response;
            $results2 = json_decode($response2->getContent(), true);

            $this->assertTrue($response2->isOk());

            // This is the last answer of this round
            if ($i == 2) {
                $gameQuestionAnswer = \App\Models\GameQuestionAnswer::ofUser(2)
                    ->ofGame($gameId)
                    ->orderBy('id', 'DESC')
                    ->first();

                $now = Carbon::now();
                $gameQuestionAnswer->created_at = $now->subSeconds(50);
                $gameQuestionAnswer->save();
                $game = Game::find($gameId);
                $this->assertTrue(Terminator::isNeedOfBeingFinishedRound($game));

                // Reset the value
                $gameQuestionAnswer->created_at = Carbon::now();
                $gameQuestionAnswer->save();
            }

            $answers = $results2['answers'];

            $this->assertTrue(count($answers) == 4);

            $randomAnswerId = $answers[array_rand($answers)]['id'];

            $this->assertDatabaseHas('game_question_answers', [
                'user_id' => 2,
                'question_answer_id' => null,
            ]);

            $this->setAPIUser(2);

            // Give the answer
            $response3 = $this->post('/api/v1/games/'.$gameId.'/question', [
                'question_answer_id' => $randomAnswerId,
            ])->response;

            $this->assertTrue($response3->isOk());

            $results3 = json_decode($response3->getContent(), true);
            $correctQuestionAnswerId = $results3['correct_answer_id'];

            // The given and the correct answer are equal. Give the player a point
            if ($randomAnswerId == $correctQuestionAnswerId) {
                $scorePlayer1++;
            }
        }

        Log::debug('reminder for player Tim');

        return [
            'gameId' => $gameId,
            'scorePlayer1' => $scorePlayer1,
            'scorePlayer2' => $scorePlayer2,
        ];
    }

    /**
     * @depends testPart4
     */
    public function testPart5($data)
    {
        $gameId = $data['gameId'];
        $scorePlayer1 = $data['scorePlayer1'];
        $scorePlayer2 = $data['scorePlayer2'];

        /*
         * Player4 answers questions
         */
        for ($i = 0; $i < 3; $i++) {
            $this->setAPIUser(4);

            // Get question
            $response2 = $this->get('/api/v1/games/'.$gameId.'/question')->response;

            $this->assertTrue($response2->isOk());

            $results2 = json_decode($response2->getContent(), true);
            $answers = $results2['answers'];

            $this->assertTrue(count($answers) == 4);

            $randomAnswerId = $answers[array_rand($answers)]['id'];

            $this->setAPIUser(4);

            // Give the answer
            $response3 = $this->post('/api/v1/games/'.$gameId.'/question', [
                'question_answer_id' => $randomAnswerId,
            ])->response;

            $this->assertTrue($response3->isOk());

            $results3 = json_decode($response3->getContent(), true);
            $correctQuestionAnswerId = $results3['correct_answer_id'];

            // The given and the correct answer are equal. Give the player a point
            if ($randomAnswerId == $correctQuestionAnswerId) {
                $scorePlayer2++;
            }
        }

        return [
            'gameId' => $gameId,
            'scorePlayer1' => $scorePlayer1,
            'scorePlayer2' => $scorePlayer2,
        ];
    }

    /**
     * Round 3.
     * @depends testPart5
     */
    public function testPart6($data)
    {
        $gameId = $data['gameId'];
        $scorePlayer1 = $data['scorePlayer1'];
        $scorePlayer2 = $data['scorePlayer2'];

        /*
         * Player2 begins answering questions
         */
        for ($i = 0; $i < 3; $i++) {
            $this->setAPIUser(2);

            // Get question
            $response2 = $this->get('/api/v1/games/'.$gameId.'/question')->response;

            $this->assertTrue($response2->isOk());

            $results2 = json_decode($response2->getContent(), true);
            $answers = $results2['answers'];

            $this->assertTrue(count($answers) == 4);

            $randomAnswerId = $answers[array_rand($answers)]['id'];

            $this->assertDatabaseHas('game_question_answers', [
                'user_id' => 2,
                'question_answer_id' => null,
            ]);

            $this->setAPIUser(2);

            // Give the answer
            $response3 = $this->post('/api/v1/games/'.$gameId.'/question', [
                'question_answer_id' => $randomAnswerId,
            ])->response;

            $this->assertTrue($response3->isOk());

            $results3 = json_decode($response3->getContent(), true);
            $correctQuestionAnswerId = $results3['correct_answer_id'];

            // The given and the correct answer are equal. Give the player a point
            if ($randomAnswerId == $correctQuestionAnswerId) {
                $scorePlayer1++;
            }
        }

        return [
            'gameId' => $gameId,
            'scorePlayer1' => $scorePlayer1,
            'scorePlayer2' => $scorePlayer2,
        ];
    }

    /**
     * @depends testPart6
     */
    public function testPart7($data)
    {
        $gameId = $data['gameId'];
        $scorePlayer1 = $data['scorePlayer1'];
        $scorePlayer2 = $data['scorePlayer2'];

        /*
         * Player4 answers questions
         */
        for ($i = 0; $i < 3; $i++) {
            $this->setAPIUser(4);

            // Get question
            $response2 = $this->get('/api/v1/games/'.$gameId.'/question')->response;

            $this->assertTrue($response2->isOk());

            $results2 = json_decode($response2->getContent(), true);
            $answers = $results2['answers'];

            $this->assertTrue(count($answers) == 4);

            $randomAnswerId = $answers[array_rand($answers)]['id'];

            $this->setAPIUser(4);

            // Give the answer
            $response3 = $this->post('/api/v1/games/'.$gameId.'/question', [
                'question_answer_id' => $randomAnswerId,
            ])->response;

            $this->assertTrue($response3->isOk());

            $results3 = json_decode($response3->getContent(), true);
            $correctQuestionAnswerId = $results3['correct_answer_id'];

            // The given and the correct answer are equal. Give the player a point
            if ($randomAnswerId == $correctQuestionAnswerId) {
                $scorePlayer2++;
            }
        }

        return [
            'gameId' => $gameId,
            'scorePlayer1' => $scorePlayer1,
            'scorePlayer2' => $scorePlayer2,
        ];
    }

    /**
     * Round 4.
     * @depends testPart7
     */
    public function testPart8($data)
    {
        $gameId = $data['gameId'];
        $scorePlayer1 = $data['scorePlayer1'];
        $scorePlayer2 = $data['scorePlayer2'];

        /*
         * Player2 begins answering questions
         */
        for ($i = 0; $i < 3; $i++) {
            $this->setAPIUser(2);

            // Get question
            $response2 = $this->get('/api/v1/games/'.$gameId.'/question')->response;

            $this->assertTrue($response2->isOk());

            $results2 = json_decode($response2->getContent(), true);
            $answers = $results2['answers'];

            $this->assertTrue(count($answers) == 4);

            $randomAnswerId = $answers[array_rand($answers)]['id'];

            $this->assertDatabaseHas('game_question_answers', [
                'user_id' => 2,
                'question_answer_id' => null,
            ]);

            $this->setAPIUser(2);

            // Give the answer
            $response3 = $this->post('/api/v1/games/'.$gameId.'/question', [
                'question_answer_id' => $randomAnswerId,
            ])->response;

            $this->assertTrue($response3->isOk());

            $results3 = json_decode($response3->getContent(), true);
            $correctQuestionAnswerId = $results3['correct_answer_id'];

            // The given and the correct answer are equal. Give the player a point
            if ($randomAnswerId == $correctQuestionAnswerId) {
                $scorePlayer1++;
            }
        }

        return [
            'gameId' => $gameId,
            'scorePlayer1' => $scorePlayer1,
            'scorePlayer2' => $scorePlayer2,
        ];
    }

    /**
     * @depends testPart8
     */
    public function testPart9($data)
    {
        $gameId = $data['gameId'];
        $scorePlayer1 = $data['scorePlayer1'];
        $scorePlayer2 = $data['scorePlayer2'];

        /*
         * Player4 answers questions
         */
        for ($i = 0; $i < 3; $i++) {
            $this->setAPIUser(4);

            // Get question
            $response2 = $this->get('/api/v1/games/'.$gameId.'/question')->response;

            $this->assertTrue($response2->isOk());

            $results2 = json_decode($response2->getContent(), true);
            $answers = $results2['answers'];

            $this->assertTrue(count($answers) == 4);

            $randomAnswerId = $answers[array_rand($answers)]['id'];

            // If we are in the first round, use the joker
            if ($i == 0) {
                $answerIds = [];
                foreach ($answers as $key => $answer) {
                    $answerIds[$key] = $answer['id'];
                }

                $this->setAPIUser(4);

                $response21 = $this->post('/api/v1/games/'.$gameId.'/joker', [
                    'answer_ids' => $answerIds,
                ])->response;

                $results21 = json_decode($response21->getContent(), true);
                $wrongAnswers = $results21['wrong'];

                $this->assertTrue(count($wrongAnswers) == 2);

                foreach ($wrongAnswers as $wrongAnswer) {
                    $keyToRemove = array_search($wrongAnswer, $answerIds);
                    unset($answers[$keyToRemove]);
                }

                $this->assertTrue(count($answers) == 2);

                $randomAnswerId = $answers[array_rand($answers)]['id'];
            }

            // This should not work
            if ($i == 1) {
                $this->setAPIUser(4);

                $response22 = $this->post('/api/v1/games/'.$gameId.'/joker', [
                    'answer_ids' => [],
                ]);

                $response22->seeJson([
                    'message' => 'Du darfst keinen Joker mehr verwenden.',
                ]);

                $response22 = $response22->response;

                $this->assertTrue(! $response22->isOk());
            }

            $this->setAPIUser(4);

            // Give the answer
            $response3 = $this->post('/api/v1/games/'.$gameId.'/question', [
                'question_answer_id' => $randomAnswerId,
            ])->response;

            $this->assertTrue($response3->isOk());

            $results3 = json_decode($response3->getContent(), true);
            $correctQuestionAnswerId = $results3['correct_answer_id'];

            // The given and the correct answer are equal. Give the player a point
            if ($randomAnswerId == $correctQuestionAnswerId) {
                $scorePlayer2++;
            }
        }

        return [
            'gameId' => $gameId,
            'scorePlayer1' => $scorePlayer1,
            'scorePlayer2' => $scorePlayer2,
        ];
    }

    /**
     * Round 5.
     * @depends testPart9
     */
    public function testPart91($data)
    {
        $gameId = $data['gameId'];
        $scorePlayer1 = $data['scorePlayer1'];
        $scorePlayer2 = $data['scorePlayer2'];

        /*
         * Player2 begins answering questions
         */
        for ($i = 0; $i < 3; $i++) {
            $this->setAPIUser(2);

            // Get question
            $response2 = $this->get('/api/v1/games/'.$gameId.'/question')->response;

            $this->assertTrue($response2->isOk());

            $results2 = json_decode($response2->getContent(), true);
            $answers = $results2['answers'];

            $this->assertTrue(count($answers) == 4);

            $randomAnswerId = $answers[array_rand($answers)]['id'];

            $this->assertDatabaseHas('game_question_answers', [
                'user_id' => 2,
                'question_answer_id' => null,
            ]);

            $this->setAPIUser(2);

            // Give the answer
            $response3 = $this->post('/api/v1/games/'.$gameId.'/question', [
                'question_answer_id' => $randomAnswerId,
            ])->response;

            $this->assertTrue($response3->isOk());

            $results3 = json_decode($response3->getContent(), true);
            $correctQuestionAnswerId = $results3['correct_answer_id'];

            // The given and the correct answer are equal. Give the player a point
            if ($randomAnswerId == $correctQuestionAnswerId) {
                $scorePlayer1++;
            }
        }

        $this->setAPIUser(2);

//        // Get the game to test, if its available
//        $this->get('/api/v1/games/active')->seeJson([
//            'id' => $gameId,
//            'status' => 'opponentsTurn'
//        ]);

        $this->setAPIUser(2);

//        $this->get('/api/v1/games/' . $gameId)->seeJson([
//            'id' => $gameId,
//            "player1_id" => 2,
//            "player2_id" => 4,
//            "player1" => "Fabiano",
//            "player2" => "Tim Tester",
//            "status" => \App\Models\Game::STATUS_TURN_OF_PLAYER_2
//        ]);

        Log::debug('no more reminder');

        return [
            'gameId' => $gameId,
            'scorePlayer1' => $scorePlayer1,
            'scorePlayer2' => $scorePlayer2,
        ];
    }

    /**
     * @depends testPart91
     */
    public function testPart92($data)
    {
        $gameId = $data['gameId'];
        $scorePlayer1 = $data['scorePlayer1'];
        $scorePlayer2 = $data['scorePlayer2'];

        /*
         * Player4 answers questions
         */
        for ($i = 0; $i < 3; $i++) {
            $this->setAPIUser(4);

            // Get question
            $response2 = $this->get('/api/v1/games/'.$gameId.'/question')->response;

//            $this->assertTrue($response2->isOk());

            $results2 = json_decode($response2->getContent(), true);
            $answers = $results2['answers'];

            $this->assertTrue(count($answers) == 4);

            $randomAnswerId = $answers[array_rand($answers)]['id'];

            $this->setAPIUser(4);

            // Give the answer
            $response3 = $this->post('/api/v1/games/'.$gameId.'/question', [
                'question_answer_id' => $randomAnswerId,
            ])->response;

            $this->assertTrue($response3->isOk());

            $results3 = json_decode($response3->getContent(), true);
            $correctQuestionAnswerId = $results3['correct_answer_id'];

            // The given and the correct answer are equal. Give the player a point
            if ($randomAnswerId == $correctQuestionAnswerId) {
                $scorePlayer2++;
            }
        }
        Log::debug('no more reminder');

        return [
            'gameId' => $gameId,
            'scorePlayer1' => $scorePlayer1,
            'scorePlayer2' => $scorePlayer2,
        ];
    }

    /**
     * Determine winner.
     * @depends testPart92
     */
    public function testPart93($data)
    {
        $gameId = $data['gameId'];
        $scorePlayer1 = $data['scorePlayer1'];
        $scorePlayer2 = $data['scorePlayer2'];

        $game = \App\Models\Game::find($gameId);
        if ($scorePlayer1 > $scorePlayer2) {
            $winnerIs = 2;
        } elseif ($scorePlayer1 < $scorePlayer2) {
            $winnerIs = 4;
        } else {
            $winnerIs = 0;
        }

        $gameEngine = new \App\Services\GameEngine();
        $winnerStats = $gameEngine->determineWinnerOfGame($game);

        $this->assertTrue($winnerStats['winnerId'] == $winnerIs);
    }

    public function testPlayerIsActiveAndAlreadyPlayedOneOrMoreGames()
    {
        $app = \App\Models\App::find(1);

        $usersBefore = \App\Models\User::where('users.app_id', $app->id)
            ->active()
            ->where('tos_accepted', 1)
            ->whereRaw('id IN (SELECT player1_id as player FROM games WHERE app_id = '.$app->id.' UNION DISTINCT SELECT player2_id as player FROM games WHERE app_id = '.$app->id.')')
            ->get();

        $player = \App\Models\User::find(2);
        $player->active = 0;
        $player->save();

        $usersAfter = \App\Models\User::where('users.app_id', $app->id)
            ->active()
            ->where('tos_accepted', 1)
            ->whereRaw('id IN (SELECT player1_id as player FROM games WHERE app_id = '.$app->id.' UNION DISTINCT SELECT player2_id as player FROM games WHERE app_id = '.$app->id.')')
            ->get();

        $this->assertTrue($usersBefore->count() == ($usersAfter->count() + 1));
    }
}
