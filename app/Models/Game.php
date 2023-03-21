<?php

namespace App\Models;

use App\Jobs\LetBotPlayGame;
use App\Mail\Mailer;
use App\Services\GameEngine;
use App\Services\Terminator;
use Carbon\Carbon;
use DB;

/**
 * Class Game.
 *
 * @property int $app_id
 * @property int $player1_id
 * @property int $player2_id
 * @property int $player1_joker_available
 * @property int $player2_joker_available
 * @property int $status
 * @property int $winner
 * @property-read \App\Models\App $app
 * @property-read \App\Models\User $player1
 * @property-read \App\Models\User $player2
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\GameRound[] $gameRounds
 * @property int $id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Game ofUser($userId)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Game active()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Game finished()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Game whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Game whereAppId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Game wherePlayer1Id($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Game wherePlayer2Id($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Game wherePlayer1JokerAvailable($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Game wherePlayer2JokerAvailable($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Game whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Game whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Game whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int $legacy_turn_order
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\GameQuestion[] $gameQuestions
 * @property-read int|null $game_questions_count
 * @property-read int|null $game_rounds_count
 * @method static \Illuminate\Database\Eloquent\Builder|Game newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Game newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Game ofApp($appId)
 * @method static \Illuminate\Database\Eloquent\Builder|Game query()
 * @method static \Illuminate\Database\Eloquent\Builder|Game whereLegacyTurnOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Game whereWinner($value)
 * @mixin IdeHelperGame
 */
class Game extends KeelearningModel
{
    use \App\Traits\Saferemovable;

    // Game statuses
    const STATUS_CANCELED = -1;
    const STATUS_FINISHED = 0;
    const STATUS_TURN_OF_PLAYER_1 = 1;
    const STATUS_TURN_OF_PLAYER_2 = 2;

    public function app()
    {
        return $this->belongsTo(\App\Models\App::class);
    }

    public function player1()
    {
        return $this->belongsTo(\App\Models\User::class)
            ->withoutGlobalScope('human');
    }

    public function player2()
    {
        return $this->belongsTo(\App\Models\User::class)
            ->withoutGlobalScope('human');
    }

    public function gameRounds()
    {
        return $this->hasMany(\App\Models\GameRound::class);
    }

    public function gameQuestions()
    {
        return $this->hasManyThrough(\App\Models\GameQuestion::class, \App\Models\GameRound::class);
    }

    /**
     * Determine if the user is as brave as to be one of the heroic players of this duel!
     *
     * @param $userId
     *
     * @return bool
     */
    public function isDuelist($userId = null)
    {
        if ($userId == null) {
            $userId = user()->id;
        }

        // The user is a real man
        if ($this->player1_id == $userId || $this->player2_id == $userId) {
            return true;
        }

        return false;
    }

    /**
     * Returns the current GameRound model or false if none exists.
     *
     * @return bool|mixed
     */
    public function getCurrentRound()
    {
        $data = $this->getCurrentRoundAndIndex();

        return $data['round'];
    }

    /**
     * The function returns the number of the current round, beginning with 1. If none exists, -1 is returned.
     *
     * @return int|string
     */
    public function getCurrentRoundIndex()
    {
        $data = $this->getCurrentRoundAndIndex();

        return $data['index'];
    }

    /**
     * Returns the current question or false if there is none.
     *
     * @return bool|mixed
     */
    public function getCurrentQuestion()
    {
        if ($information = $this->getCurrentRoundInformation(user()->id)) {
            return $information['gameQuestion'];
        }

        // If there are no question, return false
        return false;
    }

    /**
     * Returns a collection of the current QuestionAnswers or false if there are none.
     *
     * @return bool|mixed
     */
    public function getCurrentQuestionAnswers()
    {
        if ($information = $this->getCurrentRoundInformation(user()->id)) {
            return $information['gameQuestionAnswers'];
        }

        // If there are no more questionAnswers, return false
        return false;
    }

    /**
     * The function retrieves the current round information or returns false, if there is no more round to play.
     *
     * @param $userId
     * @return array|bool
     */
    public function getCurrentRoundInformation($userId)
    {
        $roundInformation = $this->getRoundInformation($userId);

        return $roundInformation;
    }

    /**
     * Scope a query to only include games that belong to this user.
     *
     * @param $query
     * @param $userId
     * @return mixed
     */
    public function scopeOfUser($query, $userId)
    {
        return $query->where(function ($query) use ($userId) {
            $query->where('games.player1_id', '=', $userId)
                ->orWhere('games.player2_id', '=', $userId);
        });
    }

    /**
     * Scope the query to only include active games.
     *
     * @param $query
     * @return mixed
     */
    public function scopeActive($query)
    {
        return $query->where('games.status', '>', self::STATUS_FINISHED);
    }

    /**
     * Scope the query to only include finished games.
     *
     * @param $query
     * @return mixed
     */
    public function scopeFinished($query)
    {
        return $query->where('games.status', '<=', self::STATUS_FINISHED);
    }

    /**
     * The function scopes results to those of the app with the given id.
     *
     * @param $query
     * @param $appId
     * @return mixed
     */
    public function scopeOfApp($query, $appId)
    {
        return $query->where('games.app_id', '=', $appId);
    }

    /**
     * Retrieves the current round and its index starting from 1. The current round is determined by the number of answers
     * given for each question of that round. If there are not two answers (not every of these two players gave an
     * answer), this is seen as current round.
     *
     * @return array
     */
    private function getCurrentRoundAndIndex()
    {
        // Check if the game is already over
        if ($this->status <= 0) {
            return [
                'index' => -1,
                'round' => false,
            ];
        }

        // Look for questions with less than two answers
        /** @var GameRound $round */
        foreach ($this->gameRounds as $index => $round) {
            // Check if this round has any questions with less than 2 answers
            /** @var GameQuestion $gameQuestion */
            foreach ($round->gameQuestions as $gameQuestion) {
                // If there are less than two answers for this question
                if ($gameQuestion->gameQuestionAnswers->count() < 2) {
                    return [
                        'index' => ($index + 1),
                        'round' => $round,
                    ];
                }
            }
        }

        // Check if the last question has a pending answer which has already timed out
        $lastRound = $this->gameRounds->last();
        if (! $lastRound) {
            return [
                'index' => -1,
                'round' => false,
            ];
        }
        $lastQuestion = $lastRound->gameQuestions->last();
        if ($lastQuestion) {
            foreach ($lastQuestion->gameQuestionAnswers as $gameQuestionAnswer) {
                $now = Carbon::now();
                $answerCreated = Carbon::parse($gameQuestionAnswer->created_at);
                if ($gameQuestionAnswer->isNotAnswered() && $answerCreated->diffInSeconds($now) > Terminator::ROUND_NEEDS_TO_BE_FINISHED_THRESHOLD) {
                    return [
                        'index' => $this->gameRounds->count(),
                        'round' => $lastRound,
                    ];
                }
            }
        }

        // Return -1 and false, if no fitting round could be found
        return [
            'index' => -1,
            'round' => false,
        ];
    }

    /**
     * Retrieve information of the current round, question and answers for the user with the given id.
     *
     * @param $userId
     * @return array|bool
     */
    private function getRoundInformation($userId)
    {
        /** @var GameRound $round */
        foreach ($this->gameRounds as $roundKey => $round) {
            $counter = 0;

            /** @var GameQuestion $gameQuestion */
            foreach ($round->gameQuestions as $gameQuestion) {
                // Track which question we are currently looking at
                $counter++;

                $gameQuestionAnswersCount = $gameQuestion
                    ->gameQuestionAnswers
                    ->where('user_id', $userId)
                    ->count();

                // If there is an answer for this question of this player, increase the counter. Return the question
                // with answers instead
                if ($gameQuestionAnswersCount == 0) {
                    return [
                        'round' => $round,
                        'gameQuestionAnswers' => $gameQuestion->gameQuestionAnswers,
                        'gameQuestion' => $gameQuestion,
                        'questionNumber' => $counter,
                        'roundNumber' => ($roundKey + 1),
                    ];
                }
            }
        }

        // If there are no more rounds to play, return false
        return false;
    }

    /**
     * Returns the id of the winner of the game.
     *
     * @return int
     */
    public function getWinner()
    {
        if ($this->status != self::STATUS_FINISHED) {
            return null;
        }
        if ($this->winner !== null) {
            return $this->winner;
        }

        $gameEngine = new GameEngine();
        $winner = $gameEngine->determineWinnerOfGame($this);
        $this->winner = $winner['winnerId'];
        $this->save();

        return $winner['winnerId'];
    }

    public function hasWon(User $player)
    {
        return $this->getWinner() === $player->id;
    }

    public function hasLost(User $player)
    {
        return $this->getWinner() > 0 && $this->getWinner() !== $player->id;
    }

    public function isDraw()
    {
        return $this->getWinner() === 0;
    }

    /**
     * The function changes the status of the game.
     */
    public function finishPlayerRound()
    {
        // There is no condition under which we want finished or canceled games to be changed in any way
        if ($this->status == self::STATUS_FINISHED || $this->status == self::STATUS_CANCELED) {
            return;
        }

        // Go through all the rounds and check if they are already finished
        $gameIsFinished = true;

        /** @var GameRound $round */
        foreach ($this->gameRounds as $round) {
            if (! $round->isFinished()) {
                $gameIsFinished = false;
                break;
            }
        }

        // If all rounds are finished, wrap up the game
        if ($gameIsFinished) {
            /** @var GameEngine $gameEngine */
            $gameEngine = app()->make(GameEngine::class);
            $gameEngine->finalizeGame($this);

            return;
        }

        // for all new games, each player does 2 turns in a row, with the exception of first and last round
        $currentRoundIndex = 0;
        foreach ($this->gameRounds as $round) {
            if ($round->isFinished()) {
                $currentRoundIndex += 1;
            }
        }
        $currentRound = $this->gameRounds[$currentRoundIndex];

        // on odd rounds, player 1 goes first, on even, player 2
        if ($currentRoundIndex % 2 == 0) {
            if ($currentRound->isFinishedFor($this->player1_id)) {
                $this->status = self::STATUS_TURN_OF_PLAYER_2;
            } else {
                $this->status = self::STATUS_TURN_OF_PLAYER_1;
            }
        } else {
            if ($currentRound->isFinishedFor($this->player2_id)) {
                $this->status = self::STATUS_TURN_OF_PLAYER_1;
            } else {
                $this->status = self::STATUS_TURN_OF_PLAYER_2;
            }
        }

        if ($this->status == self::STATUS_TURN_OF_PLAYER_1) {
            $player = $this->player1;
            $opponent = $this->player2;
        } else {
            $player = $this->player2;
            $opponent = $this->player1;
        }

        // still the same user's turn, no need to send any email
        if (! $this->isDirty('status')) {
            $this->startBotGame($player, $opponent);

            return;
        }
        $this->save();

        $mailer = app()->make(Mailer::class);
        // round switch in the first round can only mean we've finished the invitational round by player 1
        if ($currentRoundIndex == 0) {
            if (! $this->startBotGame($player, $opponent)) {
                $mailer->sendInvitation($this->id);
            }
        } else {
            if (! $this->startBotGame($player, $opponent)) {
                $mailer->sendRoundReminder($this, $player, $opponent);
            }
        }
    }

    /**
     *  Starts a bot game job if the player is a bot.
     * @param $player
     * @param $opponent
     * @return bool value if player is a bot
     */
    protected function startBotGame($player, $opponent)
    {
        if ($player->is_bot) {
            LetBotPlayGame::dispatch($this, $player, $opponent)
                ->delay(now()->addSeconds(1));
        }

        return $player->is_bot;
    }

    /**
     * The function checks if the game challenge got accepted by the opponent, e.g. if the second user already answered a question.
     *
     * @return bool
     */
    public function isChallengeAccepted()
    {
        $answersPerRound = $this->app->questions_per_round;
        $givenAnswersCount = DB::table('game_question_answers')
            ->select('game_question_answers.id')
            ->join('game_questions', 'game_questions.id', '=', 'game_question_answers.game_question_id')
            ->join('game_rounds', 'game_rounds.id', '=', 'game_questions.game_round_id')
            ->where('game_id', $this->id)
            ->count();

        // If we have less or equal answers than one user can give in one round, we are in round 1 and
        if ($givenAnswersCount <= $answersPerRound) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Gets the last answer for this game.
     *
     * @return mixed|static
     */
    public function getLastQuestionAnswer()
    {
        return DB::table('game_question_answers')
            ->select('game_question_answers.*')
            ->join('game_questions', 'game_questions.id', '=', 'game_question_answers.game_question_id')
            ->join('game_rounds', 'game_rounds.id', '=', 'game_questions.game_round_id')
            ->where('game_id', $this->id)
            ->orderBy('game_question_answers.id', 'DESC')
            ->first();
    }

    /**
     * Returns a textual representation of the current game status.
     *
     * @param null $userId
     *
     * @return string
     */
    public function getStatusString($userId = null)
    {
        if (is_null($userId)) {
            $userId = user()->id;
        }

        // Get the status for when the game is still active
        $intStatus = $this->status;
        $myTurn = 'myTurn';
        $opponentsTurn = 'opponentsTurn';
        $finished = 'finished';
        $draw = 'draw';
        $canceled = 'canceled';

        switch ($intStatus) {
            case self::STATUS_TURN_OF_PLAYER_1:
                if ($this->player1_id == $userId) {
                    return $myTurn;
                } else {
                    return $opponentsTurn;
                }

            case self::STATUS_TURN_OF_PLAYER_2:
                if ($this->player2_id == $userId) {
                    return $myTurn;
                } else {
                    return $opponentsTurn;
                }

            case self::STATUS_FINISHED:
                $gameResults = app()->make(\App\Services\GameEngine::class)->determineWinnerOfGame($this);
                if ($gameResults['state'] == GameEngine::DRAW) {
                    return $draw;
                }

                return $finished;

            case self::STATUS_CANCELED:
                return $canceled;
        }

        return $finished;
    }

    public function getOldGamePath() {
        return '/game/'.$this->id;
    }

    public function getCandyGamePath() {
        return '/quizzes/'.$this->id;
    }
}
