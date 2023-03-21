<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\QuestionAnswer;
use Illuminate\Database\Seeder;

class QuestionsTableSeeder extends Seeder
{
    /**
     * Generates 100 questions with 4 answers each (1 true, 3 false).
     *
     * @return void
     */
    public function run()
    {
        Question::factory()->count(600)
                ->create()
                ->each(function ($question) {

                    //add wrong answers
                    QuestionAnswer::factory()->count(3)->create(['question_id' => $question->id]);

                    //add true answer
                    QuestionAnswer::factory()->count(1)->create([
                            'correct'     => true,
                            'question_id' => $question->id,
                    ]);
                });
    }
}
