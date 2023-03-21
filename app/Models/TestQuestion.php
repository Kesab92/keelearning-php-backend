<?php

namespace App\Models;

/**
 * App\Models\TestQuestion
 *
 * @property int $id
 * @property int $test_id
 * @property int $position
 * @property int $question_id
 * @property int|null $points
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TestSubmissionAnswer[] $answers
 * @property-read int|null $answers_count
 * @property-read mixed $realpoints
 * @property-read \App\Models\Question $question
 * @property-read \App\Models\Test $test
 * @method static \Illuminate\Database\Eloquent\Builder|TestQuestion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TestQuestion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TestQuestion query()
 * @method static \Illuminate\Database\Eloquent\Builder|TestQuestion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestQuestion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestQuestion wherePoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestQuestion wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestQuestion whereQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestQuestion whereTestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestQuestion whereUpdatedAt($value)
 * @mixin IdeHelperTestQuestion
 */
class TestQuestion extends KeelearningModel
{
    protected $appends = ['realpoints'];

    /**
     * Relations.
     */
    public function test()
    {
        return $this->belongsTo(Test::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function answers()
    {
        return $this->hasMany(TestSubmissionAnswer::class);
    }

    public function getRealpointsAttribute()
    {
        if ($this->points) {
            return $this->points;
        }
        if (! $this->question->category) {
            return 1;
        }

        return $this->question->category->points ?: 1;
    }
}
