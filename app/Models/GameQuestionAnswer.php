<?php

namespace App\Models;

/**
 * App\Models\GameQuestionAnswer.
 *
 * @property-read \App\Models\GameQuestion $gameQuestion
 * @property-read \App\Models\User $user
 * @property-read \App\Models\QuestionAnswer $questionAnswer
 * @property int $id
 * @property int $game_question_id
 * @property int $user_id
 * @property int $question_answer_id Is -1 if the question wasn't answered in time
 * @property array $multiple
 * @property int $result null -> not yet answered, -1 -> not answered in time, 0 -> wrong, 1 -> correct
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GameQuestionAnswer ofUser($userId)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GameQuestionAnswer ofGame($gameId)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GameQuestionAnswer whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GameQuestionAnswer whereGameQuestionId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GameQuestionAnswer whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GameQuestionAnswer whereQuestionAnswerId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GameQuestionAnswer whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GameQuestionAnswer whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GameQuestionAnswer ofRound($roundId)
 * @method static \Illuminate\Database\Eloquent\Builder|GameQuestionAnswer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GameQuestionAnswer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GameQuestionAnswer query()
 * @method static \Illuminate\Database\Eloquent\Builder|GameQuestionAnswer whereMultiple($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GameQuestionAnswer whereResult($value)
 * @mixin IdeHelperGameQuestionAnswer
 */
class GameQuestionAnswer extends KeelearningModel
{
    public function gameQuestion()
    {
        return $this->belongsTo(\App\Models\GameQuestion::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class)->withoutGlobalScope('human');
    }

    public function questionAnswer()
    {
        return $this->belongsTo(\App\Models\QuestionAnswer::class);
    }

    /**
     * Scopes the gameQuestionAnswers to only get the ones of the user with the given id.
     *
     * @param $query
     * @param $userId
     * @return mixed
     */
    public function scopeOfUser($query, $userId)
    {
        return $query->where('game_question_answers.user_id', '=', $userId);
    }

    public function scopeOfGame($query, $gameId)
    {
        return $query->whereHas('gameQuestion.gameRound.game', function ($q) use ($gameId) {
            $q->where('games.id', $gameId);
        });
    }

    /**
     * Scope the questionAnswers to those which belong to the round with the id given in the input.
     *
     * @param $query
     * @param $roundId
     * @return mixed
     */
    public function scopeOfRound($query, $roundId)
    {
        return $query->whereHas('gameQuestion.gameRound', function ($query) use ($roundId) {
            $query->where('game_rounds.id', $roundId);
        });
    }

    public function getMultipleAttribute($value)
    {
        return array_filter(explode(',', $value));
    }

    public function setMultipleAttribute($value)
    {
        $this->attributes['multiple'] = implode(',', $value);
    }

    /**
     * Checks if this question hasn't been answered yet.
     *
     * @return bool
     */
    public function isNotAnswered()
    {
        return $this->result === null && $this->question_answer_id === null && ! $this->multiple;
    }
}
