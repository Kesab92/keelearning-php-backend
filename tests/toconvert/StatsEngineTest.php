<?php

namespace Tests;

use App\Models\App;
use App\Models\Category;
use App\Models\Game;
use App\Models\GameQuestionAnswer;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\User;
use App\Services\GameEngine;
use App\Stats\AppRanking;
use App\Stats\PlayerAppRanking;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class StatsEngineTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testCategories()
    {

        // Create an app
        $app = App::factory()->create();

        // Create a category & question
        $category = Category::factory()->create(['app_id' => $app->id]);
        $question = Question::factory()->create(['category_id' => $category->id, 'app_id' => $app->id]);
        for ($i = 0; $i < 20; $i++) {
            Question::factory()->create(['category_id' => $category->id, 'app_id' => $app->id]);
        }

        // Add wrong answers
        $a1 = QuestionAnswer::factory()->create(['question_id' => $question->id, 'correct'=>false]);
        $a2 = QuestionAnswer::factory()->create(['question_id' => $question->id, 'correct'=>false]);
        $a3 = QuestionAnswer::factory()->create(['question_id' => $question->id, 'correct'=>false]);
        $wrong = [$a1->id, $a2->id, $a3->id];

        // Add true answer
        $correct = QuestionAnswer::factory()->count(1)->create([
                'correct'     => true,
                'question_id' => $question->id,
        ]);

        // Create a game
        $gameEngine = new GameEngine();
        $gameId = $gameEngine->spawnGame(1, 2, $app->id);
        $round = \App\Models\GameRound::where('game_id', $gameId)->first();
        $gameQuestion = \App\Models\GameQuestion::ofRound($round->id)->first();

        // Add 2 correct and 3 wrong answers
        GameQuestionAnswer::factory()->count(1)->create([
                'user_id'            => 1,
                'game_question_id'   => $gameQuestion->id,
                'question_answer_id' => $correct->id,
        ]);
        GameQuestionAnswer::factory()->count(1)->create([
                'user_id'            => 1,
                'game_question_id'   => $gameQuestion->id,
                'question_answer_id' => $correct->id,
        ]);
        GameQuestionAnswer::factory()->count(1)->create([
                'user_id'            => 1,
                'game_question_id'   => $gameQuestion->id,
                'question_answer_id' => $wrong[array_rand($wrong)],
        ]);
        GameQuestionAnswer::factory()->count(1)->create([
                'user_id'            => 1,
                'game_question_id'   => $gameQuestion->id,
                'question_answer_id' => $wrong[array_rand($wrong)],
        ]);
        GameQuestionAnswer::factory()->count(1)->create([
                'user_id'            => 1,
                'game_question_id'   => $gameQuestion->id,
                'question_answer_id' => $wrong[array_rand($wrong)],
        ]);

        // Check if the percentage matches
        $stats = new \App\Services\StatsEngine($app->id);

        $categories = $stats->categories();

        // We expect 40 percent to be correct (2 out of 5)
        $this->assertEquals(reset($categories), 40);
    }

    public function testAppStats()
    {

        // Create an app
        $app = App::factory()->create(['rounds_per_game' => 2, 'questions_per_round' => 2]);

        // Create users
        $player1 = User::factory()->create(['app_id' => $app->id]);
        $player2 = User::factory()->create(['app_id' => $app->id]);

        // Create two categories & two questions each
        $category1 = Category::factory()->create(['app_id' => $app->id]);
        $question11 = Question::factory()->create(['category_id' => $category1->id, 'app_id' => $app->id]);
        $question12 = Question::factory()->create(['category_id' => $category1->id, 'app_id' => $app->id]);

        $category2 = Category::factory()->create(['app_id' => $app->id]);
        $question21 = Question::factory()->create(['category_id' => $category2->id, 'app_id' => $app->id]);
        $question22 = Question::factory()->create(['category_id' => $category2->id, 'app_id' => $app->id]);

        /**
         * Question answers.
         */
        // Add wrong answers for question 11
        $a111 = QuestionAnswer::factory()->create(['question_id' => $question11->id, 'correct'=>false]);
        $a112 = QuestionAnswer::factory()->create(['question_id' => $question11->id, 'correct'=>false]);
        $a113 = QuestionAnswer::factory()->create(['question_id' => $question11->id, 'correct'=>false]);
        $wrong11 = [$a111->id, $a112->id, $a113->id];

        // Add true answer for question 11
        $correct11 = QuestionAnswer::factory()->count(1)->create([
            'correct'     => true,
            'question_id' => $question11->id,
        ]);

        // Add wrong answers for question 12
        $a121 = QuestionAnswer::factory()->create(['question_id' => $question12->id, 'correct'=>false]);
        $a122 = QuestionAnswer::factory()->create(['question_id' => $question12->id, 'correct'=>false]);
        $a123 = QuestionAnswer::factory()->create(['question_id' => $question12->id, 'correct'=>false]);
        $wrong12 = [$a121->id, $a122->id, $a123->id];

        // Add true answer for question 12
        $correct12 = QuestionAnswer::factory()->count(1)->create([
            'correct'     => true,
            'question_id' => $question12->id,
        ]);

        // Add wrong answers for question 21
        $a211 = QuestionAnswer::factory()->create(['question_id' => $question21->id, 'correct'=>false]);
        $a212 = QuestionAnswer::factory()->create(['question_id' => $question21->id, 'correct'=>false]);
        $a213 = QuestionAnswer::factory()->create(['question_id' => $question21->id, 'correct'=>false]);
        $wrong21 = [$a211->id, $a212->id, $a213->id];

        // Add true answer for question 21
        $correct21 = QuestionAnswer::factory()->count(1)->create([
            'correct'     => true,
            'question_id' => $question21->id,
        ]);

        // Add wrong answers for question 22
        $a221 = QuestionAnswer::factory()->create(['question_id' => $question22->id, 'correct'=>false]);
        $a222 = QuestionAnswer::factory()->create(['question_id' => $question22->id, 'correct'=>false]);
        $a223 = QuestionAnswer::factory()->create(['question_id' => $question22->id, 'correct'=>false]);
        $wrong22 = [$a221->id, $a222->id, $a223->id];

        // Add true answer for question 22
        $correct22 = QuestionAnswer::factory()->count(1)->create([
            'correct'     => true,
            'question_id' => $question22->id,
        ]);

        // Create a game
        $gameEngine = new GameEngine();
        $gameId = $gameEngine->spawnGame($player1->id, $player2->id, $app->id);
        $round = \App\Models\GameRound::where('game_id', $gameId)->first();
        $gameQuestions = \App\Models\GameQuestion::ofRound($round->id)->get();

        /*
         * Round 1
         */
        GameQuestionAnswer::factory()->count(1)->create([
            'user_id'            => $player1->id,
            'game_question_id'   => $gameQuestions->get(0)->id,
            'question_answer_id' => $correct11->id,
        ]);
        GameQuestionAnswer::factory()->count(1)->create([
            'user_id'            => $player1->id,
            'game_question_id'   => $gameQuestions->get(0)->id,
            'question_answer_id' => $correct12->id,
        ]);

        GameQuestionAnswer::factory()->count(1)->create([
            'user_id'            => $player2->id,
            'game_question_id'   => $gameQuestions->get(0)->id,
            'question_answer_id' => $wrong11[array_rand($wrong11)],
        ]);
        GameQuestionAnswer::factory()->count(1)->create([
            'user_id'            => $player2->id,
            'game_question_id'   => $gameQuestions->get(0)->id,
            'question_answer_id' => $wrong12[array_rand($wrong12)],
        ]);
        /*
         * Round 2
         */
        GameQuestionAnswer::factory()->count(1)->create([
            'user_id'            => $player1->id,
            'game_question_id'   => $gameQuestions->get(1)->id,
            'question_answer_id' => $correct21->id,
        ]);
        GameQuestionAnswer::factory()->count(1)->create([
            'user_id'            => $player1->id,
            'game_question_id'   => $gameQuestions->get(1)->id,
            'question_answer_id' => $correct22->id,
        ]);

        GameQuestionAnswer::factory()->count(1)->create([
            'user_id'            => $player2->id,
            'game_question_id'   => $gameQuestions->get(1)->id,
            'question_answer_id' => $wrong21[array_rand($wrong21)],
        ]);
        GameQuestionAnswer::factory()->count(1)->create([
            'user_id'            => $player2->id,
            'game_question_id'   => $gameQuestions->get(1)->id,
            'question_answer_id' => $wrong22[array_rand($wrong22)],
        ]);

        $appRanking = (new AppRanking($app->id))->fetch();
        $this->assertTrue(count($appRanking) == 2);

        $userRanking1 = (new PlayerAppRanking($app->id, $player1->id))->fetch();
        $userRanking2 = (new PlayerAppRanking($app->id, $player2->id))->fetch();

        $this->assertTrue($userRanking1 == 1);
        $this->assertTrue($userRanking2 == 2);
    }
}
