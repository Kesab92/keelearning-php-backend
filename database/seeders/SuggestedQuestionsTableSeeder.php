<?php

namespace Database\Seeders;

use App\Models\SuggestedQuestion;
use App\Models\SuggestedQuestionAnswer;
use Database\Seeders\QuestionsTableSeeder;
use Illuminate\Database\Seeder;

class SuggestedQuestionsTableSeeder extends Seeder
{
    /**
     * Generates 500 suggested questions with 4 (suggested) answers each (1 true, 3 false).
     *
     * @return void
     */
    public function run()
    {
        SuggestedQuestion::factory()->count(500)
                ->create()
                ->each(function ($question) {

                    //add wrong answers
                    SuggestedQuestionAnswer::factory()->count(3)->create(['suggested_question_id' => $question->id]);

                    //add true answer
                    SuggestedQuestionAnswer::factory()->count(1)->create([
                            'correct'               => true,
                            'suggested_question_id' => $question->id,
                    ]);
                });
    }
}
