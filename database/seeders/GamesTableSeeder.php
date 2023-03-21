<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\GameQuestion;
use App\Models\GameQuestionAnswer;
use App\Models\GameRound;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GamesTableSeeder extends Seeder
{
    /**
     * Generates 70.000 games, with 1-5 game_rounds each, with 0-3 game_questions each, with 0-2
     * game_question_answers each.
     *
     * @return void
     */
    public function run()
    {

        //generate 70000 games
        for ($i = 0; $i < 70000; $i++) {
            $game = Game::factory()->count(1)->create();

            $allGameCategories = DB::table('categories')
                                   ->where('app_id', $game->app_id)
                                   ->get();

            //generate between 1 and 5 game rounds
            $numberOfRounds = rand(1, 5);
            for ($j = 0; $j < $numberOfRounds; $j++) {
                $gameRound = GameRound::factory()->count(1)->create([
                        'game_id'     => $game->id,
                        'category_id' => $allGameCategories[array_rand($allGameCategories, 1)]->id,
                ]);

                $allCategoryQuestions = DB::table('questions')
                                          ->where('category_id', $gameRound->category_id)
                                          ->get();

                $numberOfGameQuestions = 3;
                $isCurrentRound = false;

                //if current round, create 1-3 game questions.
                if ($j == $numberOfRounds - 1) {
                    $isCurrentRound = true;
                    $numberOfGameQuestions = rand(0, 3);
                }

                //generate game questions
                for ($k = 0; $k < $numberOfGameQuestions; $k++) {
                    $gameQuestion = GameQuestion::factory()->count(1)->create([
                            'game_round_id' => $gameRound->id,
                            'question_id'   => $allCategoryQuestions[array_rand($allCategoryQuestions, 1)]->id,
                    ]);

                    //get the 4 answers related to the question
                    $allQuestionAnswers = DB::table('question_answers')
                                            ->where('question_id', $gameQuestion->question_id)
                                            ->get();

                    //check for currentGameQuestion on current round
                    if ($isCurrentRound && $k == $numberOfGameQuestions - 1) {
                        $player1Answered = rand(0, 1) == 1;
                        $player2Answered = rand(0, 1) == 1;

                        if ($player1Answered) {
                            //generating answer for player 1
                            GameQuestionAnswer::factory()->count(1)->create([
                                    'user_id'            => $game->player1->id,
                                    'game_question_id'   => $gameQuestion->id,
                                    'question_answer_id' => $allQuestionAnswers[array_rand($allQuestionAnswers, 1)]->id,
                            ]);
                        }

                        if ($player2Answered) {
                            //generating answer for player 2
                            GameQuestionAnswer::factory()->count(1)->create([
                                    'user_id'            => $game->player2->id,
                                    'game_question_id'   => $gameQuestion->id,
                                    'question_answer_id' => $allQuestionAnswers[array_rand($allQuestionAnswers, 1)]->id,
                            ]);
                        }

                        //set correct game status
                        if ($player1Answered && $player2Answered && $numberOfRounds == 5 && $numberOfGameQuestions == 3) {
                            $game->status = Game::STATUS_FINISHED;
                        } else {
                            if (($player1Answered && $player2Answered) || (! $player1Answered && ! $player2Answered)) {
                                $game->status = Game::STATUS_TURN_OF_PLAYER_1;
                            } elseif ($player1Answered && ! $player2Answered) {
                                $game->status = Game::STATUS_TURN_OF_PLAYER_2;
                            } elseif ($player2Answered && ! $player1Answered) {
                                $game->status = Game::STATUS_TURN_OF_PLAYER_1;
                            }
                        }

                        $game->save();
                    } else {
                        //generating 1 answer for each user
                        GameQuestionAnswer::factory()->count(1)->create([
                                'user_id'            => $game->player1->id,
                                'game_question_id'   => $gameQuestion->id,
                                'question_answer_id' => $allQuestionAnswers[array_rand($allQuestionAnswers, 1)]->id,
                        ]);
                        GameQuestionAnswer::factory()->count(1)->create([
                                'user_id'            => $game->player2->id,
                                'game_question_id'   => $gameQuestion->id,
                                'question_answer_id' => $allQuestionAnswers[array_rand($allQuestionAnswers, 1)]->id,
                        ]);
                    }
                }
            }
        }
    }
}
