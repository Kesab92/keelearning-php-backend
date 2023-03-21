<?php

namespace App\Models;

/**
 * App\Models\GameQuestion.
 *
 * @property-read \App\Models\GameRound $gameRound
 * @property-read \App\Models\Question $question
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\GameQuestionAnswer[] $gameQuestionAnswers
 * @property int $id
 * @property int $game_round_id
 * @property int $question_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GameQuestion ofRound($roundId)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GameQuestion ofQuestion($questionId)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GameQuestion whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GameQuestion whereGameRoundId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GameQuestion whereQuestionId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GameQuestion whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GameQuestion whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read int|null $game_question_answers_count
 * @method static \Illuminate\Database\Eloquent\Builder|GameQuestion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GameQuestion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GameQuestion query()
 * @mixin IdeHelperGameQuestion
 */
class GameQuestion extends KeelearningModel
{
    public function gameRound()
    {
        return $this->belongsTo(\App\Models\GameRound::class);
    }

    public function question()
    {
        return $this->belongsTo(\App\Models\Question::class);
    }

    public function gameQuestionAnswers()
    {
        return $this->hasMany(\App\Models\GameQuestionAnswer::class);
    }

    /**
     * Scopes the query to gameQuestions of the round with the given id.
     *
     * @param $query
     * @param $roundId
     * @return mixed
     */
    public function scopeOfRound($query, $roundId)
    {
        return $query->where('game_round_id', '=', $roundId);
    }

    /**
     * Scope the gameQuestions that are retrieved to those that belong to a question with the input id.
     *
     * @param $query
     * @param $questionId
     * @return mixed
     */
    public function scopeOfQuestion($query, $questionId)
    {
        return $query->where('question_id', '=', $questionId);
    }
}
