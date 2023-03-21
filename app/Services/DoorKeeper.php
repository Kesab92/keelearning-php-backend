<?php

namespace App\Services;

use App\Http\APIError;
use App\Models\App;
use App\Models\Game;
use App\Models\GameQuestion;
use App\Models\GameQuestionAnswer;
use App\Models\GameRound;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\User;
use Illuminate\Support\Collection;

class DoorKeeper
{
    /**
     * Checks if the user is allowed to get information of this game.
     *
     * @param Game $game
     * @param null $userId
     * @return bool
     */
    public static function userIsAllowedToGetGameInformation(Game $game, $userId = null)
    {
        return $game->isDuelist($userId);
    }

    /**
     * The doorkeeper cares about the current gamestate and if the user is allowed to play.
     *
     * @param Game $game
     * @param null $userId
     *
     * @return APIError|bool
     */
    public static function userIsAllowedToPlay(Game $game, $userId = null)
    {
        if ($userId == null) {
            $userId = user()->id;
        }

        // User is may not be allowed to play, because it's the other players turn or the user is no duelist
        if (! User::isAllowedToPlay($game, $userId)) {
            return new APIError(__('errors.game_not_your_turn'));
        }

        // The game is already finished
        if ($game->status == Game::STATUS_FINISHED) {
            return new APIError(__('errors.game_not_running'));
        }

        return true;
    }

    /**
     * The function returns a boolean if the user is allowed to use the joker or an error if it could not be determined.
     *
     * @param Game $game
     * @param User $user
     * @param null $currentQuestion
     * @return APIError|bool
     */
    public static function userIsAllowedToUseJoker(Game $game, $user, $currentQuestion = null)
    {
        // Determine if the question has only 2 options to choose the answer  then disable joker
        if ($currentQuestion) {
            if ($currentQuestion->questionAnswers->count() == 2) {
                return new APIError(__('errors.generic'));
            }
        }

        // Determine if the user is player 1 or 2 and return if the joker is available
        $userIsPlayer1Or2 = $user->isPlayer1Or2($game, $user->id);

        if ($userIsPlayer1Or2['userIsPlayer1'] == 1) {
            return $game->player1_joker_available == 1;
        } elseif ($userIsPlayer1Or2['userIsPlayer2'] == 1) {
            return $game->player2_joker_available == 1;
        }

        return new APIError(__('errors.generic'));
    }

    /**
     * The function returns a boolean or GameQuestionAnswer. If the function returns true, the status fits the given questions.
     * If a GameQuestionAnswer is returned, there was a problem with the returned GameQuestionAnswer.
     *
     * @param Game $game
     * @param $userId
     * @return APIError|GameQuestionAnswer|bool
     */
    public static function gameStatusIsFitting(Game $game, $userId)
    {
        $questionsPerRound = $game->app->questions_per_round;

        // Check if it's the user's turn
        if (! self::userIsAllowedToPlay($game, $userId)) {
            return new APIError(__('errors.game_not_your_turn'));
        }

        /** @var Collection $usersAnswers */
        $usersAnswers = $game->gameRounds->reduce(function (Collection $answers, GameRound $round) use ($userId) {
            $roundAnswers = $round->gameQuestions->reduce(function (Collection $answers, GameQuestion $question) use ($userId) {
                return $answers->merge($question->gameQuestionAnswers->where('user_id', $userId));
            }, collect([]));

            return $answers->merge($roundAnswers);
        }, collect([]));

        // Get the last gameQuestionAnswer of the user
        /** @var GameQuestionAnswer $lastGameQuestionAnswer */
        $lastGameQuestionAnswer = $usersAnswers
            ->sortByDesc('id')
            ->first();

        // Check if the question_id is null or there exists no answer
        if (! $lastGameQuestionAnswer || ! is_null($lastGameQuestionAnswer->result)) {
            return true;
        }

        // If the question exists and is unanswered, check if this is the last answer for this round for this user (count + position)
        /** @var GameRound $gameRound */
        $gameRound = $game->gameRounds->first(function (GameRound $round) use ($lastGameQuestionAnswer) {
            return $round->gameQuestions->find($lastGameQuestionAnswer->game_question_id);
        });
        $questionAnswersOfRound = $gameRound
            ->gameQuestions
            ->reduce(function (Collection $answers, GameQuestion $question) use ($userId) {
                return $answers->merge($question->gameQuestionAnswers->where('user_id', $userId));
            }, collect([]))
            ->sortBy('id');

        // We co not have the critical maximum of answers for the observed round
        if ($questionAnswersOfRound->count() < $questionsPerRound) {
            return true;
        } else {
            return $lastGameQuestionAnswer;
        }
    }
}
