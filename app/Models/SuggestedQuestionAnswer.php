<?php

namespace App\Models;

/**
 * App\Models\SuggestedQuestionAnswer.
 *
 * @property-read \App\Models\SuggestedQuestion $suggestedQuestion
 * @property int $id
 * @property int $suggested_question_id
 * @property string $content
 * @property bool $correct
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SuggestedQuestionAnswer whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SuggestedQuestionAnswer whereSuggestedQuestionId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SuggestedQuestionAnswer whereContent($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SuggestedQuestionAnswer whereCorrect($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SuggestedQuestionAnswer whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SuggestedQuestionAnswer whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|SuggestedQuestionAnswer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SuggestedQuestionAnswer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SuggestedQuestionAnswer query()
 * @mixin IdeHelperSuggestedQuestionAnswer
 */
class SuggestedQuestionAnswer extends KeelearningModel
{
    public function suggestedQuestion()
    {
        return $this->belongsTo(\App\Models\SuggestedQuestion::class);
    }
}
