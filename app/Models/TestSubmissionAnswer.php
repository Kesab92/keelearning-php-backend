<?php

namespace App\Models;

/**
 * App\Models\TestSubmissionAnswer
 *
 * @property int $id
 * @property int|null $test_question_id
 * @property int $question_id
 * @property int $test_submission_id
 * @property string|null $answer_ids
 * @property int|null $result
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Question $question
 * @property-read \App\Models\TestQuestion|null $testQuestion
 * @property-read \App\Models\TestSubmission $testSubmission
 * @method static \Illuminate\Database\Eloquent\Builder|TestSubmissionAnswer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TestSubmissionAnswer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TestSubmissionAnswer query()
 * @method static \Illuminate\Database\Eloquent\Builder|TestSubmissionAnswer whereAnswerIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestSubmissionAnswer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestSubmissionAnswer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestSubmissionAnswer whereQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestSubmissionAnswer whereResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestSubmissionAnswer whereTestQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestSubmissionAnswer whereTestSubmissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestSubmissionAnswer whereUpdatedAt($value)
 * @mixin IdeHelperTestSubmissionAnswer
 */
class TestSubmissionAnswer extends KeelearningModel
{
    /**
     * Relations.
     */
    public function testSubmission()
    {
        return $this->belongsTo(TestSubmission::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function testQuestion()
    {
        return $this->belongsTo(TestQuestion::class);
    }
}
