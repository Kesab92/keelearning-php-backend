<?php

namespace App\Services;

use App\Mail\Mailer;
use App\Models\AnalyticsEvent;
use App\Models\App;
use App\Models\Category;
use App\Models\Categorygroup;
use App\Models\CategoryHider;
use App\Models\Game;
use App\Models\GamePoint;
use App\Models\GameQuestion;
use App\Models\GameQuestionAnswer;
use App\Models\GameRound;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\User;
use App\Push\Deepstream;
use App\Services\AppSettings;
use App\Stats\PlayerAppRanking;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Collection;

class GameEngine
{
    const PLAYER1_WON = 0;
    const PLAYER2_WON = 1;
    const DRAW = 2;

    /** The time that has to pass until a round is considered to not be finished the ordinary way. (in s)*/
    const ROUND_NEEDS_TO_BE_FINISHED_THRESHOLD = 40;

    /**
     * The function creates a whole game with all dependencies an returns the game id.
     *
     * @param $player1_id
     * @param $player2_id
     * @param $app_id
     *
     * @return int
     */
    public function spawnGame($player1_id, $player2_id, $app_id)
    {
        /** @var Game $game */
        $game = $this->createGame($player1_id, $player2_id, $app_id);

        // Get the app to retrieve the number of rounds and answers per question
        /** @var App $app */
        $app = App::find($app_id);
        $appProfile = $game->player1->getAppProfile();
        // Create all dependencies
        if ($appProfile->getValue('quiz_users_choose_categories')) {
            $this->createDummyGameRoundsAndQuestions($game, $app->rounds_per_game, $app->questions_per_round);
        } else {
            $this->createGameRoundsAndQuestions($game, $app->rounds_per_game, $app->questions_per_round);
        }

        AnalyticsEvent::log(
            $game->player1,
            $game->player2->is_bot ? AnalyticsEvent::TYPE_QUIZ_START_VS_BOT : AnalyticsEvent::TYPE_QUIZ_START_VS_HUMAN,
            $game
        );

        return $game->id;
    }

    /**
     * The function determines the winner of a game.
     *
     * @param Game $game
     *
     * @param bool $checkIfHasMoreThanHalfWon This parameter returns a boolean value, which check if more than half of
     * questions/rounds are played already.
     * @return array
     */
    public function determineWinnerOfGame(Game $game, $checkIfHasMoreThanHalfWon = false)
    {
        // FIXME: is always calculated, even when game is still running
        if (in_array($game->app_id, [App::ID_WUESTENROT, App::ID_WOHNDARLEHEN])) {
            return $this->determineWinnerOfGameByQuestions($game, $checkIfHasMoreThanHalfWon);
        }

        return $this->determineWinnerOfGameByRounds($game, $checkIfHasMoreThanHalfWon);
    }

    public function attachWinnerInformation($games)
    {
        $correctAnswersByRound = DB::table('game_question_answers')
            ->select(['games.id as game_id', 'game_question_answers.user_id as user_id', 'game_rounds.id as round_id', DB::raw('COUNT(*) as c')])
            ->join('game_questions', 'game_question_answers.game_question_id', '=', 'game_questions.id')
            ->join('game_rounds', 'game_questions.game_round_id', '=', 'game_rounds.id')
            ->join('games', 'game_rounds.game_id', '=', 'games.id')
            ->whereIn('games.id', $games->pluck('id'))
            ->where('game_question_answers.result', 1)
            ->groupBy(['game_question_answers.user_id', 'game_rounds.id'])
            ->get();
        $games->transform(function ($game) use ($correctAnswersByRound) {
            $game->roundInformation = $correctAnswersByRound->filter(function ($information) use ($game) {
                return $information->game_id === $game->id;
            });

            return $game;
        });
    }

    private function determineWinnerOfGameByRounds(Game $game, $checkIfHasMoreThanHalfWon)
    {
        $player1_id = $game->player1_id;
        $player2_id = $game->player2_id;

        $player1_count = 0;
        $player2_count = 0;

        // Use the preloaded round information (from `attachWinnerInformation`) if available
        if ($game->roundInformation) {
            $rounds = $game->roundInformation->groupBy('round_id');
            $rounds->each(function ($roundData) use (&$player1_count, &$player2_count, $player1_id, $player2_id) {
                $player1_round_data = $roundData->where('user_id', $player1_id)->first();
                $player1_round_count = $player1_round_data ? $player1_round_data->c : 0;

                $player2_round_data = $roundData->where('user_id', $player2_id)->first();
                $player2_round_count = $player2_round_data ? $player2_round_data->c : 0;

                if ($player1_round_count !== 0 || $player2_round_count !== 0) {
                    if ($player1_round_count > $player2_round_count) {
                        $player1_count++;
                    } elseif ($player2_round_count > $player1_round_count) {
                        $player2_count++;
                    }
                }
            });
        } else {
            /** @var GameRound $round */
            foreach ($game->gameRounds as $round) {
                $player1_round_count = 0;
                $player2_round_count = 0;
                /** @var GameQuestion $gameQuestion */
                foreach ($round->gameQuestions as $gameQuestion) {

                    /** @var GameQuestionAnswer $gameQuestionAnswer */
                    foreach ($gameQuestion->gameQuestionAnswers as $gameQuestionAnswer) {
                        if ($gameQuestionAnswer->user_id == $player1_id) {
                            // If player 1 answered this question, check if the answer was correct (or not given within time and so is empty) and add a point or do nothing
                            if ($gameQuestionAnswer->result == 1) {
                                $player1_round_count++;
                            }
                        } elseif ($gameQuestionAnswer->user_id == $player2_id) {
                            // If player 2 answered this question, check if the answer was correct (or not given within time and so is empty) and add a point or do nothing
                            if ($gameQuestionAnswer->result == 1) {
                                $player2_round_count++;
                            }
                        }
                    }
                }
                if ($player1_round_count !== 0 || $player2_round_count !== 0) {
                    if ($player1_round_count > $player2_round_count) {
                        $player1_count++;
                    } elseif ($player2_round_count > $player1_round_count) {
                        $player2_count++;
                    }
                }
            }
        }

        // Draw
        $winnerId = 0;
        $winnerCount = 0;
        $state = self::DRAW;

        // Player 1 has more points
        if ($player1_count > $player2_count) {
            $winnerId = $player1_id;
            $state = self::PLAYER1_WON;
            $winnerCount = $player1_count;
        }

        // Player 2 has more points
        if ($player2_count > $player1_count) {
            $winnerId = $player2_id;
            $state = self::PLAYER2_WON;
            $winnerCount = $player2_count;
        }

        // More than half of rounds
        $hasMoreThanHalfFinished = $checkIfHasMoreThanHalfWon
            && $winnerCount > ($game->gameRounds->count() / 2);

        return [
            'state'    => $state,
            'winnerId' => $winnerId,
            'moreThanHalfFinished' => $hasMoreThanHalfFinished,
            'roundInfo' => [
                $player1_id => $player1_count,
                $player2_id => $player2_count,
            ],
        ];
    }

    private function determineWinnerOfGameByQuestions(Game $game, $checkIfHasMoreThanHalfWon)
    {
        $player1_id = $game->player1_id;
        $player2_id = $game->player2_id;

        $player1_count = 0;
        $player2_count = 0;

        /** @var GameRound $round */
        foreach ($game->gameRounds as $round) {

            /** @var GameQuestion $gameQuestion */
            foreach ($round->gameQuestions as $gameQuestion) {

                /** @var GameQuestionAnswer $gameQuestionAnswer */
                foreach ($gameQuestion->gameQuestionAnswers as $gameQuestionAnswer) {
                    if ($gameQuestionAnswer->user_id == $player1_id) {
                        // If player 1 answered this question, check if the answer was correct (or not given within time and so is empty) and add a point or do nothing
                        if ($gameQuestionAnswer->result == 1) {
                            $player1_count++;
                        }
                    } else {
                        // If player 2 answered this question, check if the answer was correct (or not given within time and so is empty) and add a point or do nothing

                        if ($gameQuestionAnswer->result == 1) {
                            $player2_count++;
                        }
                    }
                }
            }
        }

        // Determine the winner
        $winnerId = $player1_id;
        $state = self::PLAYER1_WON;
        $winnerCount = $player1_count;

        // Player 2 has more points
        if ($player2_count > $player1_count) {
            $winnerId = $player2_id;
            $state = self::PLAYER2_WON;
            $winnerCount = $player2_count;
        }

        // Draw
        if ($player2_count == $player1_count) {
            $winnerId = 0;
            $state = self::DRAW;
            $winnerCount = 0;
        }

        // More than half of questions
        $hasMoreThanHalfFinished = false;
        if ($checkIfHasMoreThanHalfWon) {
            $questionCount = $game->app->rounds_per_game * $game->app->questions_per_round;
            $hasMoreThanHalfFinished = $winnerCount > ($questionCount / 2);
        }

        return [
            'state'    => $state,
            'winnerId' => $winnerId,
            'moreThanHalfFinished' => $hasMoreThanHalfFinished,
            'roundInfo' => [
                $player1_id => $player1_count,
                $player2_id => $player2_count,
            ],
        ];
    }

    /**
     * The function creates a gameQuestionAnswer that has no question answer id.
     *
     * @param GameQuestion $gameQuestion
     * @param              $userId
     * @param bool $invalidate If true this question will be marked as "not answered"
     * @return GameQuestionAnswer
     */
    public function createEmptyGameQuestionAnswer(GameQuestion $gameQuestion, $userId, $invalidate = false)
    {
        $gameQuestionAnswer = new GameQuestionAnswer();
        $gameQuestionAnswer->game_question_id = $gameQuestion->id;
        $gameQuestionAnswer->user_id = $userId;

        if ($invalidate) {
            $gameQuestionAnswer->question_answer_id = -1;
            $gameQuestionAnswer->result = -1;
        } else {
            $gameQuestionAnswer->question_answer_id = null;
            $gameQuestionAnswer->result = null;
        }
        $gameQuestionAnswer->save();

        return $gameQuestionAnswer;
    }

    /**
     * Updates the game question answer with the user's answer.
     *
     * @param GameQuestionAnswer $gameQuestionAnswer
     * @param                    $questionAnswerid
     */
    public function updateEmptyGameQuestionAnswer(GameQuestionAnswer $gameQuestionAnswer, $questionAnswerid)
    {
        $result = $gameQuestionAnswer->gameQuestion->question->isCorrect($questionAnswerid);
        $gameQuestionAnswer->result = $result;
        if (intval($questionAnswerid) == -1) {
            $gameQuestionAnswer->question_answer_id = -1;
            $gameQuestionAnswer->result = -1;
        } else {
            if ($gameQuestionAnswer->gameQuestion->question->type == Question::TYPE_MULTIPLE_CHOICE) {
                // Multiple Choice: In this case $questionAnswerId will be an array
                $gameQuestionAnswer->multiple = $questionAnswerid;
            } else {
                // Single Choice
                $gameQuestionAnswer->question_answer_id = $questionAnswerid;
            }
        }
        $gameQuestionAnswer->save();
    }

    /**
     * The function creates empty gameQuestionAnswers to fill up the whole game with them and sends
     * an infomail to both users.
     *
     * @param      $gameId
     * @param null $mailer
     * @throws \Exception
     */
    public function finishWholeGame($gameId, $mailer = null)
    {
        if ($mailer == null) {
            $mailer = new Mailer();
        }

        /** @var Game $game */
        $game = Game::find($gameId);

        /** @var GameRound $round */
        foreach ($game->gameRounds as $round) {

            /** @var GameQuestion $gameQuestion */
            foreach ($round->gameQuestions as $gameQuestion) {
                $gameQuestionAnswers = $gameQuestion->gameQuestionAnswers;

                // No answer for that question
                if ($gameQuestionAnswers->count() == 0) {
                    $this->createEmptyGameQuestionAnswer($gameQuestion, $game->player1_id, true);
                    $this->createEmptyGameQuestionAnswer($gameQuestion, $game->player2_id, true);

                // One answer for that question
                } elseif ($gameQuestionAnswers->count() < 2) {

                    /** @var GameQuestionAnswer $gameQuestionAnswer */
                    $gameQuestionAnswer = $gameQuestionAnswers->first();

                    // If the answer was created by one player, determine which it was
                    if ($gameQuestionAnswer->user_id == $game->player1_id) {
                        $this->createEmptyGameQuestionAnswer($gameQuestion, $game->player2_id, true);
                    } else {
                        $this->createEmptyGameQuestionAnswer($gameQuestion, $game->player1_id, true);
                    }
                }

                // Make sure that questions which never got answered are marked as timed out
                foreach ($gameQuestionAnswers as $gameQuestionAnswer) {
                    if ($gameQuestionAnswer->result === null && $gameQuestionAnswer->question_answer_id === null && ! $gameQuestionAnswer->multiple) {
                        $gameQuestionAnswer->result = -1;
                        $gameQuestionAnswer->question_answer_id = -1;
                        $gameQuestionAnswer->save();
                    }
                }
            }
        }

        // Change the game status and send emails
        $result = $this->determineWinnerOfGame($game, true);
        if ($result['moreThanHalfFinished']) {
            $game->winner = $result['winnerId'];
            $game->status = Game::STATUS_FINISHED;
        } else {
            $game->status = Game::STATUS_CANCELED;
        }
        $game->save();

        // Send mail
        if (Game::STATUS_CANCELED === $game->status) {
            $mailer->sendGameAbortInformation($game);
        } elseif (Game::STATUS_FINISHED === $game->status && $game->player1 && $game->player2) {
            $mailer->sendGameFinalizeInfo($game, $game->player1, $game->player2);
            $mailer->sendGameFinalizeInfo($game, $game->player2, $game->player1);
        }
    }

    /**
     * The function returns the answers of each question for each player. Unanswered questions are
     * represented by null. If the user didn't answer a question within time it is handled as
     * incorrect answer though.
     *
     * @param GameRound $round
     * @param Game      $game
     *
     * @return array
     */
    public function getRoundResults(GameRound $round, Game $game)
    {
        $questionsAndAnswers = [];
        $now = Carbon::now();

        /** @var GameQuestion $gameQuestion */
        foreach ($round->gameQuestions as $gameQuestion) {
            // Determine if an answer was already given by the users and if it they were correct or not
            $answerIsCorrectPlayer1 = null;
            $answerIsCorrectPlayer2 = null;

            // If there already exist gameQuestionAnswers
            if ($gameQuestion->gameQuestionAnswers->count() > 0) {

                /** @var GameQuestionAnswer $answerOfPlayer1 */
                $answerOfPlayer1 = $gameQuestion->gameQuestionAnswers
                                                ->where('user_id', $game->player1_id)
                                                ->first();

                /** @var GameQuestionAnswer $answerOfPlayer2 */
                $answerOfPlayer2 = $gameQuestion->gameQuestionAnswers
                                                ->where('user_id', $game->player2_id)
                                                ->first();

                // Check if the answers were given correctly. Treat them as incorrect if there was not given an
                // answer within the specified time
                if ($answerOfPlayer1 != null) {
                    if ($answerOfPlayer1->result !== null) {
                        $answerIsCorrectPlayer1 = $answerOfPlayer1->result == 1;
                    } elseif (Carbon::parse($answerOfPlayer1->created_at)->diffInSeconds($now) > self::ROUND_NEEDS_TO_BE_FINISHED_THRESHOLD) {
                        $answerIsCorrectPlayer1 = false;
                    }
                }
                if ($answerOfPlayer2 != null) {
                    if ($answerOfPlayer2->result !== null) {
                        $answerIsCorrectPlayer2 = $answerOfPlayer2->result == 1;
                    } elseif (Carbon::parse($answerOfPlayer2->created_at)->diffInSeconds($now) > self::ROUND_NEEDS_TO_BE_FINISHED_THRESHOLD) {
                        $answerIsCorrectPlayer2 = false;
                    }
                }
            }

            // Format the response
            $answers = [
                $game->player1_id => $answerIsCorrectPlayer1,
                $game->player2_id => $answerIsCorrectPlayer2,
            ];

            $questionsAndAnswers[] = [
                'answers' => $answers,
            ];
        }

        return $questionsAndAnswers;
    }

    public function sendNewGameSync(Game $game)
    {
        $rounds = [];
        foreach ($game->gameRounds as $round) {
            // Retrieve the correct or incorrect answers of each player for each round
            $questionsAndAnswers = $this->getRoundResults($round, $game);

            $rounds[] = [
                'id'            => $round->id,
                'category'      => isset($round->category) ? $round->category->name : '',
                'category_id'   => isset($round->category) ? $round->category->id : 0,
                'category_icon' => isset($round->category) ? $round->category->icon_url : null,
                'questions'     => $questionsAndAnswers,
            ];
        }
        $data = [
            'results' => $this->determineWinnerOfGame($game),
            'rounds' => $rounds,
            'status' => $game->status,
        ];
        /** @var Deepstream $deepstream */
        $deepstream = new Deepstream($game->app, true);
        try {
            $deepstream->sendEvent('users/'.$game->player1_id.'/games/'.$game->id, $data);
            if (! $game->player2->is_bot) {
                $deepstream->sendEvent('users/'.$game->player2_id.'/games/'.$game->id, $data);
            }
        } catch (\Exception $e) {
            // It's not super bad if this didn't work. We can just report it and move on
            report($e);
        }
    }

    /**
     * The function sends a reminder mail for the user of that app with his/her current ranking
     * inside the app.
     *
     * @param Mailer $mailer
     * @param        $appId
     * @param        $userId
     */
    public function sendReminder(Mailer $mailer, $appId, $userId)
    {
        $rankingPosition = (new PlayerAppRanking($appId, $userId))->fetch();
        $mailer->sendAppReminder($appId, $userId, $rankingPosition);
    }

    /**
     * The function takes an array of questionAnswerIds an returns floor(n/2) ids of wrong
     * questionsAnswers.
     *
     * @param $questionAnswerIdsArray
     *
     * @return array
     */
    public function useJoker($questionAnswerIdsArray)
    {

        // First shuffle the answers
        shuffle($questionAnswerIdsArray);

        // Go through the ids and save the first floor(n / 2) ids of wrong answers to the array to return
        $wrongIds = [];
        $counterMax = floor(count($questionAnswerIdsArray) / 2);
        foreach ($questionAnswerIdsArray as $answerId) {

            /** @var QuestionAnswer $questionAnswer */
            $questionAnswer = QuestionAnswer::find($answerId);
            if ($questionAnswer->correct == 0) {
                $wrongIds[] = $answerId;
                $counterMax--;

                if ($counterMax == 0) {
                    break;
                }
            }
        }

        return $wrongIds;
    }

    /**
     * The function returns a game model.
     *
     * @param $player1_id
     * @param $player2_id
     *
     * @return Game
     */
    private function createGame($player1_id, $player2_id, $app_id)
    {
        $game = new Game();
        $game->app_id = $app_id;
        $game->player1_id = $player1_id;
        $game->player2_id = $player2_id;
        $game->player1_joker_available = 1;
        $game->player2_joker_available = 1;
        $game->status = Game::STATUS_TURN_OF_PLAYER_1;
        $game->save();

        return $game;
    }

    /**
     * The function creates rounds for each $numberOfRounds and calls the createGameQuestion for
     * each.
     *
     * @param Game $game
     * @param      $roundsPerGame
     * @param      $questionsPerRound
     */
    private function createGameRoundsAndQuestions(Game $game, $roundsPerGame, $questionsPerRound)
    {

        // Create distinct categories for each numberOfRounds and take one for every round
        $randomGameCategoriesIds = Category::ofApp($game->app_id)
                                           ->where('categories.active', 1)
                                           ->has('questions')
                                           ->orderByRaw('RAND()')
                                           ->pluck('id');

        // If player2 is a bot only choose from categories of player1
        $players = [$game->player1];
        if (! $game->player2->is_bot) {
            $players[] = $game->player2;
        }

        // Check that we only use categories which both players are allowed to play
        foreach ($players as $player) {
            $playerCategories = $player->getQuestionCategories();
            $randomGameCategoriesIds = $randomGameCategoriesIds->filter(function ($category) use ($playerCategories) {
                // If the category has a tag we have to make sure that the player is allowed to play in it
                return $playerCategories->contains('id', $category);
            });
        }

        // Make sure there are at least as many categories in our array as there are rounds
        while ($randomGameCategoriesIds->count() < $roundsPerGame) {
            $randomGameCategoriesIds->push($randomGameCategoriesIds->random());
        }

        $alreadyCreatedGameQuestions = [];
        for ($i = 0; $i < $roundsPerGame; $i++) {
            $categoryId = $randomGameCategoriesIds->pop();
            $gameRound = new GameRound();
            $gameRound->game_id = $game->id;
            $gameRound->category_id = $categoryId;
            $gameRound->save();

            // Save the questions ids not to get the same questions for equal categories
            for ($j = 0; $j < $questionsPerRound; $j++) {
                $gameQuestionId = $this->createGameQuestion($categoryId, $gameRound->id, $alreadyCreatedGameQuestions);

                /** @var GameQuestion $gameQuestion */
                $gameQuestion = GameQuestion::find($gameQuestionId);
                $alreadyCreatedGameQuestions[] = $gameQuestion->question_id;
            }
        }
    }

    /**
     * The function creates dummy rounds for each $numberOfRounds and calls the createGameQuestion
     * for each.
     *
     * @param Game $game
     * @param      $roundsPerGame
     * @param      $questionsPerRound
     */
    private function createDummyGameRoundsAndQuestions(Game $game, $roundsPerGame, $questionsPerRound)
    {
        for ($i = 0; $i < $roundsPerGame; $i++) {
            $gameRound = new GameRound();
            $gameRound->game_id = $game->id;
            $gameRound->category_id = null;
            $gameRound->save();

            for ($j = 0; $j < $questionsPerRound; $j++) {
                $this->createDummyGameQuestion($gameRound->id);
            }
        }
    }

    /**
     * The function creates a GameQuestion that should differ from the already created questions.
     * The method returns the id of the gameQuestion.
     *
     * @param       $categoryId
     * @param       $roundId
     * @param array $alreadyCreatedQuestions
     *
     * @return int
     */
    private function createGameQuestion($categoryId, $roundId, array $alreadyCreatedQuestions)
    {
        $gameQuestion = new GameQuestion();
        $this->populateGameQuestion($categoryId, $roundId, $alreadyCreatedQuestions, $gameQuestion);

        return $gameQuestion->id;
    }

    public function getCategoryQuestionsQuery($categoryId) {
        return Question::withoutIndexCards()
            ->ofCategoryWithId($categoryId)
            ->visible();
    }

    private function populateGameQuestion($categoryId, $roundId, array $alreadyCreatedQuestions, $gameQuestion)
    {
        // Create the query for the questions. Already created questions are taken into account to not ask the same question twice
        $query = $this->getCategoryQuestionsQuery($categoryId);

        // Make sure we only respect already asked questions when we have at least one unused question left
        if (count($alreadyCreatedQuestions) > 0) {
            $query->whereNotIn('id', $alreadyCreatedQuestions);
        }
        if ($query->count() == 0) {
            $query = Question::ofCategoryWithId($categoryId)
                ->withoutIndexCards()
                ->visible();
        }

        $query->orderByRaw('RAND()');
        $categoryQuestion = $query->first();

        $gameQuestion->game_round_id = $roundId;
        $gameQuestion->question_id = $categoryQuestion->id;
        $gameQuestion->save();

        return $gameQuestion->question_id;
    }

    /**
     * The function creates a dummy GameQuestion.
     *
     * @param $roundId
     */
    private function createDummyGameQuestion($roundId)
    {
        $gameQuestion = new GameQuestion();
        $gameQuestion->game_round_id = $roundId;
        $gameQuestion->question_id = null;
        $gameQuestion->save();
    }

    /**
     * The function returns the available categories.
     *
     * @param Game $game
     * @param      $gameRoundId
     *
     * @return array
     */
    public function getAvailableCategories(Game $game, $gameRoundId)
    {
        $settings = new AppSettings($game->app_id);
        $questionCount = $game->app->questions_per_round;
        $randomGameCategories = Category::ofApp($game->app_id)
                                        ->where('categories.active', 1)
                                        ->whereDoesntHave('hiders', function ($q) {
                                            $q->where('scope', CategoryHider::SCOPE_QUIZ);
                                        })
                                        ->whereHas('questions', function ($query) {
                                            $query->where('visible', true);
                                        }, '>', $questionCount - 1)
                                        ->orderByRaw('id')
                                        ->get();

        // Check that we only use categories which both players are allowed to play
        $players = [$game->player1];
        if (! $game->player2->is_bot) {
            $players[] = $game->player2;
        }
        foreach ($players as $player) {
            $playerCategories = $player->getQuestionCategories();
            $randomGameCategories = $randomGameCategories->filter(function ($category) use ($playerCategories) {
                // If the category has a tag we have to make sure that the player is allowed to play in it
                return $playerCategories->contains('id', $category['id']);
            });
        }

        if ($settings->getValue('sort_categories_alphabetically')) {
            $randomGameCategories = $randomGameCategories->sort(function ($categoryA, $categoryB) {
                return strtolower($categoryA->name) > strtolower($categoryB->name);
            })->values();
        }

        if ($game->app_id === App::ID_FORD) {
            // Ford wants to choose between 3 random categories
            $numAvailable = 3;
            // seed random function with game round id to always get the same results
            $randomGameCategories = $randomGameCategories->toArray();
            srand($gameRoundId);
            shuffle($randomGameCategories);

            return array_slice($randomGameCategories, 0, $numAvailable);
        } else {
            return $randomGameCategories->values()->toArray();
        }
    }

    /**
     * The function returns the available category groups.
     *
     * @param Game $game
     * @param      $gameRoundId
     *
     * @return array
     */
    public function getAvailableCategorygroups(Game $game, $gameRoundId)
    {
        $settings = new AppSettings($game->app_id);
        $questionCount = $game->app->questions_per_round;
        $gameCategorygroups = Categorygroup::ofApp($game->app_id)
                                        ->whereHas('categories', function ($q) use ($questionCount) {
                                            $q->whereDoesntHave('hiders', function ($q) {
                                                $q->where('scope', CategoryHider::SCOPE_QUIZ);
                                            });
                                        })
                                        ->with(['categories' => function ($query) use ($questionCount, $settings) {
                                            $query->whereHas('questions', function ($query) {
                                                $query->where('visible', true);
                                            }, '>', $questionCount - 1);
                                        }])
                                        ->get();
        $gameCategorygroups = new Collection($gameCategorygroups->toArray());

        $players = [$game->player1];
        if (! $game->player2->is_bot) {
            $players[] = $game->player2;
        }

        // Check that we only use categories which both players are allowed to play, which excludes hidden categories
        foreach ($players as $player) {
            /** @var User $player */
            $playerCategoryGroups = $player->getQuestionCategorygroups(CategoryHider::SCOPE_QUIZ);
            $gameCategorygroups = $gameCategorygroups->filter(function ($cg) use ($playerCategoryGroups) {
                // If the categorygroup has a tag we have to make sure that the player is allowed to play in it
                return $playerCategoryGroups->contains('id', $cg['id']);
            });

            // Filter categories from groups which are not playable
            $playerCategories = $player->getQuestionCategories(CategoryHider::SCOPE_QUIZ);
            $gameCategorygroups = $gameCategorygroups->map(function ($gameCategorygroup) use (&$playerCategories, $settings) {
                $gameCategorygroup['categories'] = array_values(array_filter($gameCategorygroup['categories'], function ($category) use ($playerCategories) {
                    return $playerCategories->contains('id', $category['id']);
                }));
                if ($settings->getValue('sort_categories_alphabetically')) {
                    usort($gameCategorygroup['categories'], function ($categoryA, $categoryB) {
                        return strtolower($categoryA['name']) > strtolower($categoryB['name']);
                    });
                }

                return $gameCategorygroup;
            });
        }

        // Remove category groups where no categories are playable
        $gameCategorygroups = $gameCategorygroups->filter(function ($gameCategorygroup) {
            return count($gameCategorygroup['categories']) > 0;
        });

        if ($settings->getValue('sort_categories_alphabetically')) {
            $gameCategorygroups = $gameCategorygroups->sort(function ($categoryA, $categoryB) {
                return strtolower($categoryA['name']) > strtolower($categoryB['name']);
            })->values();
        }

        return $gameCategorygroups->toArray();
    }

    /**
     * The function sets the currently active category.
     *
     * @param GameRound $gameRound
     * @param           $categoryId
     *
     * @internal param $game
     */
    public function setNextCategory(GameRound $gameRound, $categoryId)
    {
        $gameRound->category_id = $categoryId;
        $gameRound->save();

        // delete dummy questions
        $dummyQuestions = GameQuestion::ofRound($gameRound->id)->get();

        $alreadyCreatedGameQuestions = [];
        foreach ($dummyQuestions as $gameQuestion) {
            $questionId = $this->populateGameQuestion($categoryId, $gameRound->id, $alreadyCreatedGameQuestions, $gameQuestion);
            $alreadyCreatedGameQuestions[] = $questionId;
        }
    }

    /**
     * @param Game $game
     */
    public function awardGamePoints(Game $game)
    {
        $winnerId = $game->getWinner();
        if ($winnerId > 0) {
            // Someone won
            $gamePoint = new GamePoint();
            $gamePoint->user_id = $winnerId;
            $gamePoint->amount = 2;
            $gamePoint->reason = GamePoint::REASON_GAME_WON;
            $gamePoint->save();
        } else {
            // Handle a draw
            foreach ([$game->player1_id, $game->player2_id] as $userId) {
                $gamePoint = new GamePoint();
                $gamePoint->user_id = $userId;
                $gamePoint->amount = 1;
                $gamePoint->reason = GamePoint::REASON_GAME_DRAW;
                $gamePoint->save();
            }
        }
    }

    /**
     * Finds active games between the two users.
     *
     * @param int $user1
     * @param int $user2
     */
    public function findActiveGamesBetweenUsers($user1id, $user2id)
    {
        $games = Game::where('status', '>', 0)
                     ->where(function ($query) use ($user1id, $user2id) {
                         $query->where(function ($query) use ($user1id, $user2id) {
                             $query->where('player1_id', $user1id)
                                   ->where('player2_id', $user2id);
                         })->orWhere(function ($query) use ($user1id, $user2id) {
                             $query->where('player1_id', $user2id)
                                   ->where('player2_id', $user1id);
                         });
                     })
                     ->get();

        return $games;
    }

    /**
     * Finalizes the game.
     *
     * @param Game $game
     */
    public function finalizeGame(Game $game)
    {
        if ($game->status === Game::STATUS_TURN_OF_PLAYER_1) {
            $notifyPlayer = $game->player2;
            $opponent = $game->player1;
        } else {
            $notifyPlayer = $game->player1;
            $opponent = $game->player2;
        }

        $game->status = Game::STATUS_FINISHED;
        $game->save();
        $this->awardGamePoints($game);

        /** @var Mailer $mailer */
        $mailer = app(Mailer::class);
        $mailer->sendGameFinalizeInfo($game, $notifyPlayer, $opponent);

        // Clear the stats cache for both users
        $game->player1->clearStatsCache();
        $game->player2->clearStatsCache();
    }

    public function getAnswerResponse(Question $question, $questionAnswerId)
    {
        switch ($question->type) {
            case Question::TYPE_MULTIPLE_CHOICE:
                return $this->getMultipleChoiceAnswerResponse($question, $questionAnswerId);
            case Question::TYPE_INDEX_CARD:
                // index cards return an array for questionAnswerId,
                // but will only give `null` or the ID of the only answer attached
                return $this->getDefaultAnswerResponse($question, $questionAnswerId[0]);
            default:
                return $this->getDefaultAnswerResponse($question, $questionAnswerId);
        }
    }

    private function getMultipleChoiceAnswerResponse(Question $question, $questionAnswerId)
    {
        $correctAnswers = $question->questionAnswers()
            ->where('correct', 1)
            ->pluck('question_answers.id');
        $correctAnswers = $correctAnswers->map(function ($answer) {
            return (int) $answer;
        });
        $feedback = [];
        QuestionAnswer::whereIn(
            'id',
            collect([$questionAnswerId])->merge($correctAnswers)
        )
            ->with('translationRelation')
            ->get()
            ->each(function ($answer) use (&$feedback) {
                $feedback[$answer->id] = $answer->feedback;
            });

        return [
            'correct_answer_id' => $correctAnswers,
            'feedback' => $feedback,
        ];
    }

    private function getDefaultAnswerResponse(Question $question, $questionAnswerId)
    {
        // Look for the correct result
        foreach ($question->questionAnswers()->with('translationRelation')->get() as $otherQuestionAnswer) {
            // Return the json with the id of the correct answer
            if ($otherQuestionAnswer->correct) {
                $feedback = [];
                QuestionAnswer::whereIn('id', collect([$questionAnswerId, $otherQuestionAnswer->id])->unique())
                    ->with('translationRelation')
                    ->get()
                    ->each(function ($answer) use (&$feedback) {
                        $feedback[$answer->id] = $answer->feedback;
                    });

                if ($questionAnswerId > -1 && ! $feedback[$questionAnswerId]) {
                    $feedback = [$otherQuestionAnswer->id => $otherQuestionAnswer->feedback];
                }

                return [
                    'correct_answer_id' => $otherQuestionAnswer->id,
                    'feedback' => $feedback,
                ];
            }
        }
        return null;
    }
}
