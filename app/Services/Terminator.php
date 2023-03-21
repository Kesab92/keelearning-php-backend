<?php

namespace App\Services;

use App\Models\App;
use App\Models\AppProfile;
use App\Models\Game;
use App\Models\GameQuestion;
use App\Models\GameQuestionAnswer;
use App\Models\GameRound;
use App\Services\AppSettings;
use Carbon\Carbon;
use Log;
use Sentry;

class Terminator
{
    /** The time that has to pass until a round is considered to not be finished the ordinary way. (in s)*/
    const ROUND_NEEDS_TO_BE_FINISHED_THRESHOLD = 40;

    /**
     * The function checks, if the gameId that was given as input, is a game that has to be
     * finished artificially. This decision is returned as boolean value. If the game has to be
     * finished, this is done in addition.
     *
     * @param $activeGame
     *
     * @return bool
     */
    public static function seekAndFinishRound($activeGame, $console)
    {
        $console->line('checking game #'.$activeGame->id);
        if ($result = self::isNeedOfBeingFinishedRound($activeGame)) {
            self::finishRound($activeGame);
        }

        return $result;
    }

    /**
     * The function seeks for games with rounds that were not played within the last 24 hours.
     *
     * @param App $app
     * @param $console
     */
    public static function seekAndFinishGame(App $app, $console)
    {
        $appProfile = $app->getDefaultAppProfile();

        $relevantGamesCreatedAt = Carbon::now()->subHours($appProfile->getValue('quiz_round_answer_time'));

        /** @var Game $games */
        $games = Game::active()
                     ->whereAppId($app->id)
                     ->where('created_at', '<=', $relevantGamesCreatedAt)
                     ->get();
        if (! $games->count()) {
            $console->info('No relevant games for app #'.$app->id);

            return;
        }
        $console->info('Found '.$games->count().' currently active game for app #'.$app->id);

        $gameEngine = new GameEngine();

        $console->info('Timeout for new challenges is '.$appProfile->getValue('quiz_round_initial_answer_time')
                     .' hours, for running games '.$appProfile->getValue('quiz_round_answer_time').' hours.'
                     .' Weekends will might delay the timeouts.');

        /** @var Game $game */
        foreach ($games as $game) {
            try {
                if ($status = self::isGameTooOld($game, $appProfile)) {
                    $gameEngine->finishWholeGame($game->id, null);
                    $console->line('Finished game #'.$game->id);
                } else {
                    $console->line('No need to finish game #'.$game->id);
                }
            } catch (\Exception $e) {
                Log::error($e);
                Sentry::captureException($e);
            }
        }
    }

    /**
     * The function checks if the current question is the last of this round for the current user.
     * If the algorithm determines, that there was not given a proper answer, return true. Else
     * return false.
     *
     * @param Game $game
     *
     * @return bool
     */
    public static function isNeedOfBeingFinishedRound($game)
    {
        $lastAnswer = $game->getLastQuestionAnswer();
        if (! $lastAnswer) {
            return false;
        }
        $lastQuestion = GameQuestion::find($lastAnswer->game_question_id);
        $app = $game->app;

        /** @var GameRound $lastRound */
        $lastRound = $lastQuestion->gameRound;
        $player1Count = 0;
        $player2Count = 0;
        foreach ($lastRound->gameQuestions()
                          ->with('gameQuestionAnswers')
                          ->get() as $question) {
            foreach ($question->gameQuestionAnswers as $answer) {
                if ($answer->user_id == $game->player1_id) {
                    $player1Count++;
                } else {
                    $player2Count++;
                }
            }
        }

        // Check if player 2 / player 1 already answered all questions but is still the active player
        $currentRoundIndex = 0;
        foreach ($game->gameRounds as $round) {
            if ($round->isFinished()) {
                $currentRoundIndex += 1;
            }
        }

        // last round finished?
        if ($currentRoundIndex >= $game->gameRounds->count()) {
            $invalidRoundActive = true;
        } else {
            $currentRound = $game->gameRounds[$currentRoundIndex];
            if ($currentRoundIndex % 2 == 0) {
                if ($currentRound->isFinishedFor($game->player1_id)) {
                    $invalidRoundActive = $game->status != Game::STATUS_TURN_OF_PLAYER_2;
                } else {
                    $invalidRoundActive = $game->status != Game::STATUS_TURN_OF_PLAYER_1;
                }
            } else {
                if ($currentRound->isFinishedFor($game->player2_id)) {
                    $invalidRoundActive = $game->status != Game::STATUS_TURN_OF_PLAYER_1;
                } else {
                    $invalidRoundActive = $game->status != Game::STATUS_TURN_OF_PLAYER_2;
                }
            }
        }

        if (! $invalidRoundActive) {
            return false;
        }

        if ($lastAnswer->result == null) {
            $now = Carbon::now();
            $answerCreated = Carbon::parse($lastAnswer->created_at);

            // If the time between the creation and now is more than the threshold, that round has to be finished artificially
            if ($answerCreated->diffInSeconds($now) > self::ROUND_NEEDS_TO_BE_FINISHED_THRESHOLD) {
                return true;
            }
        }

        return false;
    }

    /**
     * CAUTION: The gameId input has to be an active game.
     *
     * The function checks if the current round's first questionAnswer is beneath a certain
     * threshold in the past, and so the game has to be finished
     *
     * @param Game $game
     * @param AppProfile $appProfile
     * @return bool
     */
    public static function isGameTooOld($game)
    {
        $timeout = self::getGameExpiration($game);

        return $timeout->isPast();
    }

    /**
     * CAUTION: The gameId input has to be an active game.
     *
     * The function returns the time when the game will expire
     *
     * @param Game $game
     * @return Carbon
     */
    public static function getGameExpiration($game)
    {
        if ($game->player1) {
            $appProfile = $game->player1->getAppProfile();
        } else {
            $appProfile = $game->app->getDefaultAppProfile();
        }

        // First check, if the game got accepted by the opponent. So the second player answered at least one question and has 24 hours
        if ($game->isChallengeAccepted()) {
            $lastQuestionAnswerOfGame = $game->getLastQuestionAnswer();
            $lastActive = Carbon::parse($lastQuestionAnswerOfGame->created_at);
            $timeout = $appProfile->getValue('quiz_round_answer_time');
        } else {
            $lastActive = Carbon::parse($game->created_at);
            $timeout = $appProfile->getValue('quiz_round_initial_answer_time');
        }

        if (! $appProfile->getValue('quiz_no_weekend_grace_period')) {
            // add 48 hours if $lastActive + $timeout ends on a weekend
            $end = $lastActive->copy()->addHours($timeout);
            if ($end->dayOfWeek === 6 || $end->dayOfWeek === 0) {
                $timeout += 48;
            }
        }

        return $lastActive->addHours($timeout);
    }

    /**
     * CAUTION: the input parameter has to be an active game, where the last question
     * of the currently active round was fetched by a user, but was never really finished. Than,
     * this game can be considered in a state, where an artificial change of the game status is
     * necessary!
     *
     * The function finishes the current round. For this purpose, the game status is changed, if
     * really all questions of this round were answered (or an empty GameQuestionAnswer was given)
     *
     * @param $activeGameId
     */
    private static function finishRound($game)
    {

        /** @var Game $game */

        // If it's the turn of player 1, search his latest answer in this game and vice versa
        if ($game->status == Game::STATUS_TURN_OF_PLAYER_1) {
            $userId = $game->player1_id;
        } else {
            $userId = $game->player2_id;
        }

        /** @var GameQuestionAnswer $lastGameQuestionAnswer */
        $lastGameQuestionAnswer = GameQuestionAnswer::ofUser($userId)
                                                    ->ofGame($game->id)
                                                    ->orderBy('id', 'DESC')
                                                    ->first();
        $lastGameQuestionAnswer->question_answer_id = -1;
        $lastGameQuestionAnswer->result = -1;
        $lastGameQuestionAnswer->save();

        $game->finishPlayerRound();
    }
}
