<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;

/**
 * App\Models\TestSubmission
 *
 * @property int $id
 * @property int $test_id
 * @property int $user_id
 * @property int|null $result
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Test $test
 * @property-read Collection|\App\Models\TestSubmissionAnswer[] $testSubmissionAnswers
 * @property-read int|null $test_submission_answers_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|TestSubmission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TestSubmission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TestSubmission query()
 * @method static \Illuminate\Database\Eloquent\Builder|TestSubmission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestSubmission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestSubmission whereResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestSubmission whereTestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestSubmission whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestSubmission whereUserId($value)
 * @mixin IdeHelperTestSubmission
 */
class TestSubmission extends KeelearningModel
{
    /**
     * Relations.
     */
    public function test()
    {
        return $this->belongsTo(Test::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function testSubmissionAnswers()
    {
        return $this->hasMany(TestSubmissionAnswer::class);
    }

    public function percentage()
    {
        $overallPoints = $this->test->points();
        if (! $overallPoints) {
            return 0;
        }
        $testPoints = $this->testSubmissionAnswers->reduce(function ($c, $a) {
            if (! $a->result) {
                return $c;
            }
            if ($a->testQuestion) {
                return $c + $a->testQuestion->realpoints;
            }
            if ($a->question) {
                return $c + $a->question->category->points;
            }

            return $c;
        });

        return round(($testPoints / $overallPoints) * 100);
    }

    public function moneycoasterAnswertime()
    {
        /** @var Collection $allAnswers */
        $allAnswers = $this->testSubmissionAnswers;
        // This actually calculates the time for the last 11 questions, because the 11th last question was started wenn the 12th last question was answered
        $last12Answers = $allAnswers->sortByDesc('updated_at')->take(12);
        // first/last is not intuitive, because the answers are sorted descending
        $firstAnswer = $last12Answers->last();
        $lastAnswer = $last12Answers->first();
        if (! $firstAnswer || ! $lastAnswer) {
            return 0;
        }

        return $lastAnswer->updated_at->diffInSeconds($firstAnswer->created_at);
    }
}
