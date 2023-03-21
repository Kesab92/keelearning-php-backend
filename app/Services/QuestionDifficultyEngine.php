<?php

namespace App\Services;

use App\Models\Question;
use App\Models\QuestionDifficulty;
use App\Models\User;
use DB;
use Illuminate\Support\Collection;

class QuestionDifficultyEngine
{
    const MAX_DIFFICULTY_ADJUSTMENTS_PER_USER = 3;

    /**
     * Recalculates both the global and the per-user difficulty of a given question.
     *
     * @param Question $question
     * @param User $user
     * @param bool $correct
     * @param int $answerTime
     */
    public function updateDifficulty(Question $question, User $user, $correct, $answerTime)
    {
        $answerTimePercentage = max(0, min(1, $answerTime / $question->realanswertime));
        $globalDifficulty = QuestionDifficulty::where('question_id', $question->id)
                                              ->whereNull('user_id')
                                              ->first();
        if (! $globalDifficulty) {
            $globalDifficulty = new QuestionDifficulty();
            $globalDifficulty->question_id = $question->id;
        }
        $userDifficulty = QuestionDifficulty::where('question_id', $question->id)
                                            ->where('user_id', $user->id)
                                            ->first();
        if (! $userDifficulty) {
            $userDifficulty = new QuestionDifficulty();
            $userDifficulty->question_id = $question->id;
            $userDifficulty->user_id = $user->id;
        }
        if ($userDifficulty->sample_size < self::MAX_DIFFICULTY_ADJUSTMENTS_PER_USER) {
            $this->recalculateDifficulty($globalDifficulty, $correct, $answerTimePercentage);
        }
        $this->recalculateDifficulty($userDifficulty, $correct, $answerTimePercentage);
    }

    /**
     * Calculates and saves the new QuestionDifficulty.
     *
     * @param QuestionDifficulty $questionDifficulty
     * @param bool $correct
     * @param $answerTimePercentage
     */
    private function recalculateDifficulty(QuestionDifficulty $questionDifficulty, $correct, $answerTimePercentage)
    {
        $answerDifficulty = $correct ? 1 : -0.5;
        $answerDifficulty -= 0.5 * $answerTimePercentage;
        $questionDifficulty->difficulty =
            ($questionDifficulty->difficulty * $questionDifficulty->sample_size + $answerDifficulty)
            / ($questionDifficulty->sample_size + 1);
        $questionDifficulty->sample_size += 1;
        $questionDifficulty->save();
    }

    /**
     * Attaches the questions global difficulty and user difficulty.
     *
     * @param Collection $questions
     */
    public function attachQuestionDifficulties(Collection &$questions, User $user)
    {
        $difficultiesGlobal = DB::table('question_difficulties')
            ->whereIn('question_id', $questions->keys())
            ->whereNull('user_id')
            ->select(['question_id', 'difficulty'])
            ->get()
            ->pluck('difficulty', 'question_id');

        $difficultiesUser = DB::table('question_difficulties')
            ->whereIn('question_id', $questions->keys())
            ->where('user_id', $user->id)
            ->select(['question_id', 'difficulty'])
            ->get()
            ->pluck('difficulty', 'question_id');

        foreach ($questions as $question) {
            $question->difficulty = (
                    isset($difficultiesGlobal[$question->id]) ? $difficultiesGlobal[$question->id] : 0 +
                    (isset($difficultiesUser[$question->id]) ? $difficultiesUser[$question->id] : 0 * 2)
                )
                / 3;
        }
    }
}
