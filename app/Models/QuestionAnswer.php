<?php

namespace App\Models;

/**
 * App\Models\QuestionAnswer.
 *
 * @property-read \App\Models\Question $question
 * @property int $id
 * @property int $question_id
 * @property string $content
 * @property bool $correct
 * @property string $feedback
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\QuestionAnswer correct()
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\GameQuestionAnswer[] $gameQuestionAnswer
 * @method static \Illuminate\Database\Query\Builder|\App\Models\QuestionAnswer whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\QuestionAnswer whereQuestionId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\QuestionAnswer whereContent($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\QuestionAnswer whereCorrect($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\QuestionAnswer whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\QuestionAnswer whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\QuestionAnswerTranslation[] $allTranslationRelations
 * @property-read int|null $all_translation_relations_count
 * @property-read int|null $game_question_answer_count
 * @property-read mixed $app_id
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAnswer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAnswer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAnswer query()
 * @mixin IdeHelperQuestionAnswer
 */
class QuestionAnswer extends KeelearningModel
{
    use \App\Traits\Duplicatable;
    use \App\Traits\Translatable;

    public $translated = ['content', 'feedback'];

    public function question()
    {
        return $this->belongsTo(\App\Models\Question::class);
    }

    public function gameQuestionAnswer()
    {
        return $this->hasMany(\App\Models\GameQuestionAnswer::class);
    }

    /**
     * Scopes the answers to those which are correct.
     *
     * @param $query
     * @return mixed
     */
    public function scopeCorrect($query)
    {
        return $query->where('correct', '=', 1);
    }

    public function getAppIdAttribute()
    {
        if (! $this->question) {
            return null;
        }

        return $this->question->app_id;
    }
}
