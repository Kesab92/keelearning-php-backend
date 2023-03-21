<?php

namespace Database\Seeders;

use App\Models\App;
use App\Models\Category;
use App\Models\Game;
use App\Models\GameQuestion;
use App\Models\GameQuestionAnswer;
use App\Models\GameRound;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\Tag;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SmallTablesSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        // App create
        $app = new App();
        $app->name = 'Fun with flags';
        $app->rounds_per_game = 5;
        $app->answers_per_question = 4;
        $app->questions_per_round = 3;
        $app->app_hosted_at = 'quizapp-frontend.dev:8000';
        $app->terms = 'Terms of the app: Fun with flags';
        $app->contact_information = '092378409; swag@fwf.de';
        $app->save();

        // Tag create
        for ($i = 0; $i < 10; $i++) {
            $tag = new Tag();
            $tag->label = $faker->word;
            $tag->creator_id = 2;
            $tag->app_id = 1;
            $tag->save();
        }

        // Users create
        DB::table('users')
            ->insert([
                'app_id'       => 1,
                'username'     => 'Paul',
                'email'        => 'p.mohr@sopamo.de',
                'password'     => '$2y$10$Vpe/V4zl6BHtiJXtcdiUoODD4Uq0gwXH2TF7jTdJu8uAZ11bg4LkS',
                'is_admin'     => true,
                'tos_accepted' => true,
            ]);
        DB::table('tag_user')->insert([
            'user_id' => 1,
            'tag_id' => 1,
        ]);
        DB::table('tag_user')->insert([
            'user_id' => 1,
            'tag_id' => 2,
        ]);
        DB::table('tag_user')->insert([
            'user_id' => 1,
            'tag_id' => 3,
        ]);
        DB::table('tag_user')->insert([
            'user_id' => 1,
            'tag_id' => 4,
        ]);
        DB::table('tag_user')->insert([
            'user_id' => 1,
            'tag_id' => 5,
        ]);

        DB::table('users')
            ->insert([
                'app_id'       => 1,
                'username'     => 'Fabiano',
                'email'        => 'f.henkel@sopamo.de',
                'password'     => '$2y$10$DAixWvp56u5QsRU6Orl2ler5DwJFKjW37cszLu4Gq5krubvoK2Bjy', //yolo
                'is_admin'     => true,
                'tos_accepted' => true,
            ]);
        DB::table('tag_user')->insert([
            'user_id' => 2,
            'tag_id' => 4,
        ]);

        DB::table('users')
            ->insert([
                'app_id'       => 1,
                'username'     => 'moe',
                'email'        => 'm.kraus@sopamo.de',
                'password'     => '$2y$10$vel82sEmtC/fZPWgNNOKauZQguRR7P70xAR8F.KjLjucPUd9Phvwe',
                'is_admin'     => true,
                'tos_accepted' => true,
            ]);
        DB::table('tag_user')->insert([
            'user_id' => 3,
            'tag_id' => 4,
        ]);
        DB::table('tag_user')->insert([
            'user_id' => 3,
            'tag_id' => 5,
        ]);

        DB::table('users')
            ->insert([
                'app_id'       => 1,
                'username'     => 'Tim Tester',
                'email'        => 't.tester@sopamo.de',
                'password'     => '$2y$10$DAixWvp56u5QsRU6Orl2ler5DwJFKjW37cszLu4Gq5krubvoK2Bjy', //yolo
                'is_admin'     => true,
                'tos_accepted' => true,
            ]);

        // Create Categories
        for ($i = 0; $i < 10; $i++) {
            $category = new Category();
            $category->app_id = 1;
            $category->name = 'Category '.($i + 1);
            $category->save();

            // Questions
            for ($j = 0; $j < 20; $j++) {
                $question = new Question();
                $question->app_id = 1;
                $question->title = 'Question Title #'.$category->id.'-'.($j + 1);
                $question->visible = 1;
                $question->category_id = $category->id;
                $question->save();

                for ($o = 0; $o < $app->answers_per_question; $o++) {
                    $questionAnswer = new QuestionAnswer();
                    $questionAnswer->question_id = $question->id;
                    $questionAnswer->content = 'QuestionAnswer #'.$question->id.'-'.($o + 1);
                    if ($o == 1) {
                        $questionAnswer->correct = 1;
                    } else {
                        $questionAnswer->correct = 0;
                    }
                    $questionAnswer->save();
                }
            }
        }

        // Create games
        for ($a = 0; $a < 10; $a++) {
            $game = new Game();
            $game->player1_id = 2;
            $game->app_id = 1;
            if (($a % 2) == 0) {
                $game->player2_id = 1;
            } else {
                $game->player2_id = 4;
            }
            $game->player1_joker_available = 1;
            $game->player2_joker_available = 1;
            $game->status = Game::STATUS_TURN_OF_PLAYER_1;
            $game->save();

            for ($b = 0; $b < $app->rounds_per_game; $b++) {
                $gameRound = new GameRound();
                $gameRound->game_id = $game->id;
                $gameRound->category_id = $faker->numberBetween(1, 10);
                $gameRound->save();

                // Calculate the ids of questions of a category
                $minId = ($gameRound->category_id - 1) * 20;
                $maxId = $minId + 20;

                for ($c = 0; $c < $app->questions_per_round; $c++) {
                    $gameQuestion = new GameQuestion();
                    $gameQuestion->game_round_id = $gameRound->id;
                    $gameQuestion->question_id = $faker->numberBetween($minId, $maxId);
                    $gameQuestion->save();
                }
            }
        }
    }
}
