<?php

namespace App\Models;

/**
 * App\Models\GameRound.
 *
 * @property-read \App\Models\Game $game
 * @property-read \App\Models\Category $category
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\GameQuestion[] $gameQuestions
 * @property int $id
 * @property int $game_id
 * @property int $category_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GameRound whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GameRound whereGameId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GameRound whereCategoryId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GameRound whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GameRound whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GameRound ofGame($gameId)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\GameQuestionAnswer[] $gameQuestionAnswers
 * @property-read int|null $game_question_answers_count
 * @property-read int|null $game_questions_count
 * @method static \Illuminate\Database\Eloquent\Builder|GameRound newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GameRound newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GameRound query()
 * @mixin IdeHelperGameRound
 */
class GameRound extends KeelearningModel
{
    public function game()
    {
        return $this->belongsTo(\App\Models\Game::class);
    }

    public function category()
    {
        return $this->belongsTo(\App\Models\Category::class);
    }

    public function gameQuestions()
    {
        return $this->hasMany(\App\Models\GameQuestion::class);
    }

    public function gameQuestionAnswers()
    {
        return $this->hasManyThrough(\App\Models\GameQuestionAnswer::class, \App\Models\GameQuestion::class);
    }

    /**
     * Scopes the results to those of the game with the given id.
     *
     * @param $query
     * @param $gameId
     * @return mixed
     */
    public function scopeOfGame($query, $gameId)
    {
        return $query->where('game_rounds.game_id', $gameId);
    }

    /**
     * Check if the round is finished for both players.
     *
     * @return bool
     */
    public function isFinished()
    {
        $player1Id = $this->game->player1_id;
        $player2Id = $this->game->player2_id;

        return $this->isFinishedFor($player1Id) && $this->isFinishedFor($player2Id);
    }

    /**
     * The function checks, if the round is finished for a user with the given id.
     *
     * @param $userId
     * @return bool
     */
    public function isFinishedFor($userId)
    {
        $counter = 0;

        // Go through the questions and count the answers
        foreach ($this->gameQuestions as $gameQuestion) {
            $gameQuestionAnswersCount = $gameQuestion
                ->gameQuestionAnswers()
                ->ofUser($userId)
                ->count();

            if ($gameQuestionAnswersCount > 0) {
                $counter++;
            }
        }
        $appGameQuestionAnswersCount = $this->game->app->questions_per_round;

        // If there are not as many answers as there should be, return false. Else return true.
        if ($counter < $appGameQuestionAnswersCount) {
            return false;
        } else {
            return true;
        }
    }
}
