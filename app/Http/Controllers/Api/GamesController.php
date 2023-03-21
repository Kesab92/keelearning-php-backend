<?php

namespace App\Http\Controllers\Api;

use App\Console\Commands\BotCreation;
use App\Http\APIError;
use App\Http\Controllers\Controller;
use App\Jobs\HandleQuizAnswer;
use App\Mail\Mailer;
use App\Models\AzureVideo;
use App\Models\Game;
use App\Models\GameQuestionAnswer;
use App\Models\GameRound;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionAttachment;
use App\Models\User;
use App\Services\AppSettings;
use App\Services\DoorKeeper;
use App\Services\GameEngine;
use App\Services\QuestionDifficultyEngine;
use App\Services\Terminator;
use App\Services\TimeLimiter;
use App\Services\UserEngine;
use Carbon\Carbon;
use DB;
use Hash;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Response;
use Str;

class GamesController extends Controller
{
    private $gameEngine;
    private $mailer;
    /**
     * @var UserEngine
     */
    private $userEngine;

    public function __construct(GameEngine $gameEngine, Mailer $mailer, UserEngine $userEngine)
    {
        $this->gameEngine = $gameEngine;
        $this->mailer = $mailer;
        $this->userEngine = $userEngine;
    }

    /**
     * The function creates a game either from the given opponent id or a random user id.
     * @param Request $request
     * @return mixed
     * @throws \Exception
     */
    public function createGame(Request $request)
    {
        $opponentId = $request->get('opponent_id');

        // If the opponent id equals NULL, a random player is taken
        if ($opponentId == null && $request->input('difficulty') == null) {
            $randomUser = $this->userEngine->findRandomOpponent(user());
            if ($randomUser instanceof APIError) {
                return $randomUser;
            }
            $opponentId = $randomUser['id'];
        } elseif ($opponentId == null && $request->input('difficulty') > 0) {
            $opponentId = User::bot()
                ->where('is_bot', $request->input('difficulty'))
                ->where('app_id', appId())
                ->value('id');

            if (! $opponentId) {
                $user = new User();
                $user->app_id = appId();
                $user->username = BotCreation::$names[$request->input('difficulty') - 1];
                $user->email = 'bot-'.$request->input('difficulty').'@keelearning.de';
                $user->password = Hash::make(Str::random(50));
                $user->is_bot = $request->input('difficulty');
                $user->tos_accepted = true;
                $user->is_admin = false;
                $user->save();
            }
        }

        if (User::withoutGlobalScope('human')->find($opponentId)->app_id != user()->app_id) {
            return new APIError(__('errors.generic'));
        }

        $maxConcurrentGames = user()->app->maxConcurrentGames();

        if ($maxConcurrentGames && $this->gameEngine->findActiveGamesBetweenUsers(user()->id, User::withoutGlobalScope('human')->find($opponentId)->id)->count() >= $maxConcurrentGames) {
            return new APIError(trans_choice('errors.too_many_games', $maxConcurrentGames, ['count' => $maxConcurrentGames]));
        }

        DB::beginTransaction();
        try {
            // Create the game with all dependencies
            $gameId = $this->gameEngine->spawnGame(user()->id, $opponentId, user()->app_id);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);
            return new APIError(__('errors.generic'));
        }

        return Response::json(['game_id' => $gameId]);
    }

    /**
     * The function returns a JSON with data(questions, answers, etc.) that belongs to the game
     * with the given id.
     *
     * @param $game_id
     *
     * @return mixed
     */
    public function getGame($game_id, AppSettings $settings)
    {
        /** @var Game $game */
        $game = Game::find($game_id);

        if (! $game) {
            return new APIError(__('errors.game_not_found'));
        }

        $game->load([
            'gameRounds.gameQuestions.gameQuestionAnswers',
        ]);

        $user = user();

        // Check if the user is one of the players of that game. Else give him nothing
        if (! DoorKeeper::userIsAllowedToGetGameInformation($game, $user->id)) {
            return new APIError(__('errors.game_not_participating'));
        }

        // If the gameStatus does not fit, check if the round has to be finished artificially
        if ($decision = DoorKeeper::gameStatusIsFitting($game, $user->id)) {
            // Set question_answer_id to -1
            if (is_a($decision, \App\Models\GameQuestionAnswer::class)) {
                // If the returned object is a gameQuestionAnswer, "answer" it artificially
                /** @var GameQuestionAnswer $gameQuestionAnswer */
                $gameQuestionAnswer = $decision;
                $gameQuestionAnswer->question_answer_id = -1;
                $gameQuestionAnswer->result = -1;
                $gameQuestionAnswer->save();

                // Change status of game
                $game = Game::find($game_id);
                $game->finishPlayerRound();
            }
        }

        // For each game round of the game
        $rounds = [];
        $game = Game::find($game_id);
        $game->load([
            'gameRounds.gameQuestions.gameQuestionAnswers',
        ]);

        $gameRounds = $game->gameRounds;
        $gameRounds->load('category.translationRelation');

        foreach ($gameRounds as $round) {
            // Retrieve the correct or incorrect answers of each player for each round
            $questionsAndAnswers = $this->gameEngine->getRoundResults($round, $game);

            $rounds[] = [
                'id'        => $round->id,
                'category'  => isset($round->category) ? $round->category->name : '',
                'category_id'  => isset($round->category) ? $round->category->id : 0,
                'category_icon' => isset($round->category) ? $round->category->icon_url : null,
                'questions' => $questionsAndAnswers,
            ];
        }

        $expiration = $game->status > Game::STATUS_FINISHED ? Terminator::getGameExpiration($game)->toDateTimeString() : null;

        $response = [
                'id'         => $game->id,
                'player1_id' => $game->player1_id,
                'player2_id' => $game->player2_id,
                'player1'    => $game->player1->displayname,
                'player2'    => $game->player2->displayname,
                'player1_avatar' => $game->player1->avatar_url,
                'player2_avatar' => $game->player2->avatar_url,
                'results'    => $this->gameEngine->determineWinnerOfGame($game),
                'status'     => $game->status,
                'status_string' => $game->getStatusString($user->id),
                'created_at' => $game->created_at->toDateTimeString(),
                'rounds'     => $rounds,
                'expiration' => $expiration,
        ];

        return Response::json($response);
    }

    /**
     * The function returns a JSON with all active games of the current user.
     *
     * @return mixed
     */
    public function getActiveGames()
    {
        $user = user();
        $activeGames = Game::ofUser($user->id)
                           ->active()
                           ->get();
        $opponentIds = [];
        $activeGames->each(function ($game) use ($user, &$opponentIds) {
            if ($game->player1_id !== $user->id) {
                $opponentIds[$game->player1_id] = true;
            }
            if ($game->player2_id !== $user->id) {
                $opponentIds[$game->player2_id] = true;
            }
        });
        $opponents = User::withoutGlobalScope('human')
            ->with('app')
            ->where('app_id', $user->app_id)
            ->whereIn('id', array_keys($opponentIds))
            ->get();

        $response = [];
        $user = user();
        //$this->gameEngine->attachWinnerInformation($activeGames);
        foreach ($activeGames as $game) {
            /** @var User $opponent */
            $opponent = $opponents
                            ->where('id', '!=', $user->id)
                            ->whereIn('id', [$game->player1_id, $game->player2_id])
                            ->first();
            if (! $opponent) {
                continue;
            }
            $gameResults = $this->gameEngine->determineWinnerOfGame($game);
            $response[] = [
                    'id'         => $game->id,
                    'created_at' => $game->created_at->toDateTimeString(),
                    'updated_at' => $game->updated_at->toDateTimeString(),
                    'status'     => $game->getStatusString($user->id),
                    'opponent'   => $opponent->displayname,
                    'round_info' => $gameResults['roundInfo'],
                    'opponent_image' => $opponent->avatar_url,
            ];
        }
        $sorted = self::sortActiveGames($response);

        return Response::json($sorted);
    }

    /**
     * Sort games by status and date (newer games first).
     */
    public static function sortActiveGames(array $response): array
    {
        usort($response, function ($a, $b) {
            $status1 = $a['status'];
            $status2 = $b['status'];
            if ($status1 < $status2) {
                return -1;
            } elseif ($status1 == $status2) {
                return $b['updated_at'] <=> $a['updated_at'];
            }

            return 1;
        });

        return $response;
    }

    /**
     * Method returns the last 5 games of a user with the corresponding winners.
     *
     * @return mixed
     */
    public function getRecentGames()
    {
        $response = $this->getHistoryData(5);

        return Response::json($response);
    }

    private function getHistoryData($count)
    {
        // Get all recent games and take the last 1000
        $recentGames = Game::orderBy('games.created_at', 'DESC')
            ->with(['player1.app', 'player1.tags', 'player2.app', 'player2.tags'])
            ->finished()
            ->ofUser(user()->id)
            ->take($count)
            ->get();

        $this->gameEngine->attachWinnerInformation($recentGames);

        $response = [];
        $user = user();
        /** @var Game $game */
        foreach ($recentGames as $game) {
            if (! $game->player1 || ! $game->player2) {
                continue;
            }
            $gameResults = $this->gameEngine->determineWinnerOfGame($game);
            $opponent = $game->player1;
            if ($opponent->id === $user->id) {
                $opponent = $game->player2;
            }

            $response[] = [
                'id'         => $game->id,
                'created_at' => $game->created_at->toDateTimeString(),
                'updated_at' => $game->updated_at->toDateTimeString(),
                'status'     => $game->getStatusString(),
                'winner'     => $gameResults['winnerId'],
                'round_info' => $gameResults['roundInfo'],
                'opponent'   => $opponent->displayname,
                'opponent_image' => $opponent->avatar_url,
            ];
        }

        return $response;
    }

    public function getHistory()
    {
        $response = $this->getHistoryData(1000);

        return Response::json($response);
    }

    /**
     * The method returns the categories available to the player.
     *
     * @param $game_id
     *
     * @return JsonResponse|APIError
     */
    public function getAvailableCategories($game_id, AppSettings $settings)
    {
        /** @var Game $game */
        $game = Game::find($game_id);

        // If the answer is an APIError, the user has to go home
        $decision = DoorKeeper::userIsAllowedToPlay($game);
        if (is_a($decision, \App\Http\APIError::class)) {
            return $decision;
        }

        // If the current round cannot be retrieved
        if (! $currentRoundInformation = $game->getCurrentRoundInformation(user()->id)) {
            return new APIError(__('errors.game_not_running'));
        }

        // If category is already set
        if ($currentRoundInformation['round']->category_id) {
            return new APIError(__('errors.category_already_chosen'));
        }

        if ($settings->getValue('use_subcategory_system')) {
            $categorygroups = $this->gameEngine->getAvailableCategorygroups($game, $currentRoundInformation['round']->id);
            $categorygroups = array_map(function ($categorygroup) {
                $categorygroup['categories'] = array_map(function ($category) {
                    $category['cover_image'] = formatAssetURL($category['cover_image']);
                    $category['cover_image_url'] = formatAssetURL($category['cover_image_url']);
                    $category['category_icon'] = formatAssetURL($category['category_icon']);
                    $category['category_icon_url'] = formatAssetURL($category['category_icon_url']);

                    return $category;
                }, $categorygroup['categories']);

                return $categorygroup;
            }, $categorygroups);

            return Response::json([
              'usegroups' => true,
              'categories' => $categorygroups,
            ]);
        }

        $categories = $this->gameEngine->getAvailableCategories($game, $currentRoundInformation['round']->id);
        $categories = array_map(function ($category) {
            $category['cover_image'] = formatAssetURL($category['cover_image']);
            $category['cover_image_url'] = formatAssetURL($category['cover_image_url']);
            $category['category_icon'] = formatAssetURL($category['category_icon']);
            $category['category_icon_url'] = formatAssetURL($category['category_icon_url']);

            return $category;
        }, $categories);

        return Response::json($categories);
    }

    /**
     * The method sets the next category for the current game.
     *
     * @param Request $request
     * @param         $game_id
     *
     * @return APIError|JsonResponse
     */
    public function setNextCategory(Request $request, AppSettings $settings, $game_id)
    {
        /** @var Game $game */
        $game = Game::find($game_id);

        // If the answer is an APIError, the user has to go home
        $decision = DoorKeeper::userIsAllowedToPlay($game);
        if (is_a($decision, \App\Http\APIError::class)) {
            return $decision;
        }

        // If the current round cannot be retrieved
        if (! $currentRoundInformation = $game->getCurrentRoundInformation(user()->id)) {
            return new APIError(__('errors.game_not_running'));
        }

        // If category is already set
        if ($currentRoundInformation['round']->category_id) {
            return new APIError(__('errors.category_already_chosen'));
        }

        if ($settings->getValue('use_subcategory_system')) {
            $availableCategorygroups = $this->gameEngine->getAvailableCategorygroups($game, $currentRoundInformation['round']->id);
            $availableCategories = [];
            foreach ($availableCategorygroups as $acg) {
                $availableCategories = array_merge($availableCategories, $acg['categories']);
            }
        } else {
            $availableCategories = $this->gameEngine->getAvailableCategories($game, $currentRoundInformation['round']->id);
        }
        $isValid = false;
        $selectedCategoryId = $request->get('category_id');

        foreach ($availableCategories as $category) {
            if ($category['id'] == $selectedCategoryId) {
                $isValid = true;
                break;
            }
        }

        if (! $isValid) {
            return new APIError(__('errors.cant_choose_category'));
        }

        $this->gameEngine->setNextCategory($currentRoundInformation['round'], $selectedCategoryId);

        return Response::JSON(['success' => true]);
    }

    /**
     * The method returns the next question, providing that the current user is involved in this
     * game and the game is not finished yet.
     *
     * @param $game_id
     *
     * @return JsonResponse|APIError
     */
    public function getNextQuestion($game_id)
    {
        $user = user();
        /** @var Game $game */
        $game = Game
            ::with('gameRounds.gameQuestions.gameQuestionAnswers')
            ->find($game_id);

        // If the answer is an APIError, the user has to go home
        $decision = DoorKeeper::userIsAllowedToPlay($game, $user->id);
        if (is_a($decision, \App\Http\APIError::class)) {
            return $decision;
        }

        // If the current round cannot be retrieved
        if (! $currentRoundInformation = $game->getCurrentRoundInformation($user->id)) {
            return new APIError(__('errors.game_not_running'));
        }

        // Check if the answer was not already answered
        $gameQuestionAnswersCount = $currentRoundInformation['gameQuestion']
            ->gameQuestionAnswers
            ->where('user_id', $user->id)
            ->count();

        // If there is already a questionAnswer for this question we abort
        if ($gameQuestionAnswersCount > 0) {
            return new APIError(__('errors.question_already_answered'));
        }

        // If the gameStatus does not fit, check if the round has to be finished artificially
        if ($decision = DoorKeeper::gameStatusIsFitting($game, $user->id)) {
            // Set question_answer_id to -1
            if (is_a($decision, \App\Models\GameQuestionAnswer::class)) {
                // If the returned object is a gameQuestionAnswer, "answer" it artificially
                /** @var GameQuestionAnswer $gameQuestionAnswer */
                $gameQuestionAnswer = GameQuestionAnswer::find($decision->id);
                $gameQuestionAnswer->question_answer_id = -1;
                $gameQuestionAnswer->result = -1;
                $gameQuestionAnswer->save();

                // Check if the last round is finished for both users. If it is, the user can continue, else the game
                // status will be switched
                $lastGameRound = $gameQuestionAnswer->gameQuestion->gameRound;
                if (! $lastGameRound->isFinished()) {
                    $game = Game::find($game_id);
                    $game->finishPlayerRound();

                    return new APIError(__('errors.connection_problem_round_forfeit'));
                }
            }
        }

        /** @var Question $question */
        $question = $currentRoundInformation['gameQuestion']->question;

        // waiting for user to select a category?
        if (! $question) {
            return Response::json(['selectCategory' => true]);
        }
        $question->load('questionAnswers.translationRelation');

        /** @var Collection $answers */
        $lc = language($game->app_id);
        $answers = $question->questionAnswers;
        $answers = $answers
                            ->shuffle()
                            ->map(function (QuestionAnswer $a) use ($lc) {
                                return [
                                    'id' => $a->id,
                                    'content' => $a->setLanguage($lc)->translation($lc)->content,
                                ];
                            });

        // Create an empty game question answer
        $this->gameEngine->createEmptyGameQuestionAnswer($currentRoundInformation['gameQuestion'], $user->id);

        $canUseJoker = DoorKeeper::userIsAllowedToUseJoker($game, $user, $question);
        if (is_a($canUseJoker, \App\Http\APIError::class)) {
            $canUseJoker = false;
        }

        $attachments = $question->attachments->map(function ($attachment) use ($game) {
            if ($attachment->type === QuestionAttachment::ATTACHMENT_TYPE_AUDIO || $attachment->type === QuestionAttachment::ATTACHMENT_TYPE_IMAGE) {
                $attachment->attachment = formatAssetURL($attachment->attachment);
                $attachment->attachment_url = formatAssetURL($attachment->attachment_url);
            }
            if ($attachment->type === QuestionAttachment::ATTACHMENT_TYPE_AZURE_VIDEO) {
                $azureVideo = AzureVideo::where('app_id', $game->app_id)->where('id', $attachment->attachment)->first();
                if ($azureVideo) {
                    $attachment->attachment = $azureVideo->streaming_url;
                } else {
                    $attachment->attachment = '';
                }
            }

            return $attachment;
        });

        $response = [
            'id'       => $question->id,
            'type'     => $question->type,
            'latex'     => $question->latex,
            'category' => $question->category->name,
            'category_parent' => $question->category->categorygroup ? $question->category->categorygroup->name : null,
            'category_image' => formatAssetURL($question->category->image_url),
            'title'    => $question->title,
            'totalQuestions' => $game->app->questions_per_round,
            'currentQuestion' => $currentRoundInformation['questionNumber'],
            'answers'  => $answers,
            'canUseJoker' => $canUseJoker,
            'attachments' => $attachments,
            'answertime' => $question->realanswertime,
        ];

        return Response::json($response);
    }

    /**
     * The method returns the information for the game/round intro panel, or tells the frontend to skip the intro / choose a category.
     *
     * @param $game_id
     *
     * @return JsonResponse|APIError
     */
    public function getIntro($game_id)
    {

        /** @var Game $game */
        $game = Game::find($game_id);

        // If the answer is an APIError, the user has to go home
        $decision = DoorKeeper::userIsAllowedToPlay($game);
        if (is_a($decision, \App\Http\APIError::class)) {
            return $decision;
        }

        // If the current round cannot be retrieved
        if (! $currentRoundInformation = $game->getCurrentRoundInformation(user()->id)) {
            return new APIError(__('errors.game_not_running'));
        }

        // If the gameStatus does not fit, check if the round has to be finished artificially
        if ($decision = DoorKeeper::gameStatusIsFitting($game, user()->id)) {
            // Set question_answer_id to -1
            if (is_a($decision, \App\Models\GameQuestionAnswer::class)) {
                // If the returned object is a gameQuestionAnswer, "answer" it artificially
                /** @var GameQuestionAnswer $gameQuestionAnswer */
                $gameQuestionAnswer = GameQuestionAnswer::find($decision->id);
                $gameQuestionAnswer->question_answer_id = -1;
                $gameQuestionAnswer->result = -1;
                $gameQuestionAnswer->save();

                // Check if the last round is finished for both users. If it is, the user can continue, else the game
                // status will be switched
                $lastGameRound = $gameQuestionAnswer->gameQuestion->gameRound;
                if (! $lastGameRound->isFinished()) {
                    $game = Game::find($game_id);
                    $game->finishPlayerRound();

                    return new APIError(__('errors.connection_problem_round_forfeit'));
                }
            }
        }

        /** @var Question $question */
        $question = $currentRoundInformation['gameQuestion']->question;

        // waiting for user to select a category?
        if (! $question) {
            return Response::json(['selectCategory' => true]);
        }

        $img = $question->category->cover_image_url;
        $fallback = '/img/empty-wide.png';

        if($game->player1->is_bot > 0 || $game->player2->is_bot > 0) {
            $isBotGame = true;
        } else {
            $isBotGame = false;
        }

        $response = [
            'image'           => formatAssetURL($img ?: $fallback), // TODO: legacy
            'cover_image_url' => formatAssetURL($img ?: asset($fallback)),
            'title'           => $question->category->name,
            'category_id'     => $question->category_id,
            'isBotGame'       => $isBotGame,
        ];

        return Response::json($response);
    }

    /**
     * The function returns the id of the correct answer for that question after saving the answer
     * given by the user.
     *
     * @param Request $request
     * @param         $game_id
     *
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function answerQuestion(Request $request, QuestionDifficultyEngine $questionDifficultyEngine, $game_id)
    {

        /** @var Game $game */
        $game = Game::find($game_id);

        // Check if the user is allowed to play this game
        $decision = DoorKeeper::userIsAllowedToPlay($game);
        if (is_a($decision, \App\Http\APIError::class)) {
            return $decision;
        }

        // Fetch the users answer to this question.
        // There will always be an answer at this point with question_answer_id set to null
        // because we created it when the user fetched the question initially.
        // This is because we can now check if he answered within the given time period.
        $questionAnswerId = $request->get('question_answer_id');
        /** @var GameQuestionAnswer $gameQuestionAnswer */
        $gameQuestionAnswer = GameQuestionAnswer::ofUser(user()->id)
            ->select('game_question_answers.*')
            ->join('game_questions', 'game_questions.id', '=', 'game_question_answers.game_question_id')
            ->join('game_rounds', 'game_rounds.id', '=', 'game_questions.game_round_id')
            ->where('game_rounds.game_id', $game->id)
            ->orderBy('game_question_answers.id', 'DESC')
            ->first();

        /** @var GameRound $round */
        $gameQuestion = $gameQuestionAnswer->gameQuestion;
        $round = $gameQuestion->gameRound;

        // Check if there was time left for the user to answer
        $withinTime = TimeLimiter::answerGivenWithinTime($gameQuestionAnswer);
        if (is_a($withinTime, \App\Http\APIError::class)) {
            // Answer was already answered
            return $withinTime;
        }
        if (! $withinTime) {
            // Too late, forfeit question
            $questionAnswerId = -1;
        }
        $this->gameEngine->updateEmptyGameQuestionAnswer($gameQuestionAnswer, $questionAnswerId);
        if ($round->isFinishedFor(user()->id)) {
            $game->finishPlayerRound();
        }

        HandleQuizAnswer::dispatchAfterResponse($game, $withinTime, $gameQuestion, user(), $gameQuestionAnswer);

        /** @var Question $question */
        $question = $gameQuestionAnswer->gameQuestion->question;

        $response = $this->gameEngine->getAnswerResponse($question, $questionAnswerId);
        if ($response) {
            $response['result'] = $gameQuestionAnswer->result;

            return Response::json($response);
        }

        // No correct answer found?
        return new APIError(__('errors.generic'));
    }

    /**
     * The function handles the request when the user wants to use a joker. floor(n/2) of the wrong id elements given
     * as input are returned as wrong.
     *
     * @param Request $request
     * @param $game_id
     * @return APIError|bool
     */
    public function useJoker(Request $request, $game_id)
    {
        $user = user();
        /** @var Game $game */
        $game = Game::find($game_id);

        // Check if the user is allowed to play this game
        $canPlay = DoorKeeper::userIsAllowedToPlay($game);
        if (is_a($canPlay, \App\Http\APIError::class)) {
            return $canPlay;
        }

        // Check if the user is allowed to use a joker
        $canUseJoker = DoorKeeper::userIsAllowedToUseJoker($game, $user);
        if (is_a($canUseJoker, \App\Http\APIError::class)) {
            return $canUseJoker;
        }
        if (! $canUseJoker) {
            return new APIError(__('errors.no_joker_available'));
        }

        // Retrieve an array of floor(n/2) wrong answers
        $wrongIds = $this->gameEngine->useJoker($request->get('answer_ids'));

        // Remove the joker for this player for this game
        if (! user()->removeJoker($game, $user->id)) {
            return new APIError(__('errors.joker_error'));
        }

        $response = [
            'wrong' => $wrongIds,
        ];

        return json_encode($response);
    }
}
