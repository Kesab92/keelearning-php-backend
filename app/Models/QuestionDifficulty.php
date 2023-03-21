<?php

namespace App\Models;

/**
 * App\Models\QuestionDifficulty
 *
 * @property int $id
 * @property int $question_id
 * @property int|null $user_id
 * @property string $difficulty
 * @property int $sample_size
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Question $question
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionDifficulty newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionDifficulty newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionDifficulty query()
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionDifficulty whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionDifficulty whereDifficulty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionDifficulty whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionDifficulty whereQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionDifficulty whereSampleSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionDifficulty whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionDifficulty whereUserId($value)
 * @mixin IdeHelperQuestionDifficulty
 */
class QuestionDifficulty extends KeelearningModel
{
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
