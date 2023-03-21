<?php

namespace Tests\Feature\Api;

use App\Models\AppSetting;
use App\Models\Category;
use App\Models\Game;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\User;
use App\Services\GameEngine;
use Tests\TestCase;

class GameTest extends TestCase
{

    private $game;
    private $user1;
    private $user2;

    public function setUp(): void
    {
        parent::setUp();

        $setting = new AppSetting();
        $setting->app_id = $this->quizApp->id;
        $setting->key = 'users_choose_categories';
        $setting->value = 1;
        $setting->save();

        $this->user1 = User::factory()->active()->create([
            'app_id' => $this->quizApp->id,
            'email' => 'test1@sopamo.de',
        ]);
        $this->user2 = User::factory()->active()->create([
            'app_id' => $this->quizApp->id,
            'email' => 'test2@sopamo.de',
        ]);
        $this->setAPIUser($this->user1->id);
        $this->makeCategoriesAndQuestions();
        $this->game = Game::find(app(GameEngine::class)->spawnGame($this->user1->id, $this->user2->id, $this->quizApp->id));
    }

    private function makeCategoriesAndQuestions()
    {
        for ($i = 0; $i < 4; $i++) {
            // TODO: use factory
            $category = new Category();
            $category->setAppId($this->quizApp->id);
            $category->name = 'Category '.$i;
            $category->active = true;
            $category->app_id = $this->quizApp->id;
            $category->save();

            for ($j = 0; $j < 5; $j++) {
                // TODO: use factory
                $question = new Question();
                $question->setAppId($this->quizApp->id);
                $question->title = 'Question '.$j;
                $question->category_id = $category->id;
                $question->app_id = $this->quizApp->id;
                $question->visible = true;
                $question->type = Question::TYPE_SINGLE_CHOICE;
                $question->save();

                for ($k = 0; $k < 4; $k++) {
                    // TODO: use factory
                    $questionAnswer = new QuestionAnswer;
                    $questionAnswer->setAppId($this->quizApp->id);
                    $questionAnswer->question_id = $question->id;
                    $questionAnswer->correct = $k == 0;
                    $questionAnswer->save();
                }
            }
        }
    }

    /**
     * Checks that the game overview works.
     *
     * @return void
     */
    public function testGameOverview()
    {
        $response = $this->json('GET', '/api/v1/games/'.$this->game->id);
        $response->assertJsonFragment([
            'id' => $this->game->id,
            'player1_id' => $this->user1->id,
            'player2_id' => $this->user2->id,
            'status' => Game::STATUS_TURN_OF_PLAYER_1,
        ]);
    }

    /**
     * Checks that the game intro works.
     *
     * @return void
     */
    public function testGameIntro()
    {
        $this->doIntro('selectCategory');
    }

    private function doIntro($expectState)
    {
        // TODO: this logic has changed
    }

    private function answerQuestion()
    {
        for ($i = 0; $i < 4; $i++) {
            // Get the question
            $question = $this->json('GET', '/api/v1/games/'.$this->game->id.'/question');
            if (array_key_exists('message', $question->json())) {
                // Round has ended
                $this->assertEquals($i, 3);
                break;
            } else {
                $this->assertLessThan(3, $i);
            }
            $question->assertJsonStructure([
                'id',
                'type',
                'latex',
                'category',
                'category_parent',
                'category_image',
                'title',
                'totalQuestions',
                'currentQuestion',
                'answers',
                'canUseJoker',
                'attachments',
                'answertime',
            ]);

            // Save the answer
            $questionAnswer = $this->json('POST', '/api/v1/games/'.$this->game->id.'/question', [
                'question_answer_id' => $question->json()['answers'][0]['id'],
            ]);
            $questionAnswer->assertJsonStructure([
                'result',
                'correct_answer_id',
                'feedback',
            ]);
        }

        $this->doIntro('notMyTurn');
    }

    private function selectCategory()
    {
        // Fetch the available categories
        $categories = $this->json('GET', '/api/v1/games/'.$this->game->id.'/categories');
        $this->assertCount(3, $categories->json(), 'Invalid category count');

        // Select the first category
        $categoryResponse = $this->json('POST', '/api/v1/games/'.$this->game->id.'/categories', [
            'category_id' => $categories->json()[0]['id'],
        ]);
        $categoryResponse->assertJson(['success'=>true]);

        // Go to the intro
        $this->doIntro('answerQuestion');
    }
}
