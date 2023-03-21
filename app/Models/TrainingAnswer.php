<?php

namespace App\Models;

/**
 * App\Models\Page.
 *
 * @property-read \App\Models\App $app
 * @property int $id
 * @property int $user_id
 * @property string $question_id
 * @property string $answer_ids
 * @property bool $correct
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @mixin \Eloquent
 * @property-read \App\Models\Question $question
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|TrainingAnswer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TrainingAnswer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TrainingAnswer query()
 * @method static \Illuminate\Database\Eloquent\Builder|TrainingAnswer whereAnswerIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrainingAnswer whereCorrect($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrainingAnswer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrainingAnswer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrainingAnswer whereQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrainingAnswer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrainingAnswer whereUserId($value)
 * @mixin IdeHelperTrainingAnswer
 */
class TrainingAnswer extends KeelearningModel
{
    public function question()
    {
        return $this->belongsTo(\App\Models\Question::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function getAnswerIdsAttribute($value)
    {
        if ($this->question->type == Question::TYPE_MULTIPLE_CHOICE) {
            return explode(',', $value);
        } else {
            return $value;
        }
    }

    public function setAnswerIdsAttribute($value)
    {
        if ($this->question->type == Question::TYPE_MULTIPLE_CHOICE) {
            $this->attributes['answer_ids'] = implode(',', $value);
        } else {
            $this->attributes['answer_ids'] = $value;
        }
    }
}
