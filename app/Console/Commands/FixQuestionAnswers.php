<?php

namespace App\Console\Commands;

use App\Models\Question;
use Illuminate\Console\Command;

class FixQuestionAnswers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fixquestionanswers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display an inspiring quote';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        foreach (Question::get() as $question) {
            if (! $question->questionAnswers()->where('correct', 1)->count()) {
                $answer = $question->questionAnswers()->first();
                $answer->correct = 1;
                $answer->save();
            }
        }
    }
}
