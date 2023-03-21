<?php

namespace App\Console\Commands;

use App\Models\GameQuestionAnswer;
use App\Models\QuestionAnswer;
use Illuminate\Console\Command;

class SetAnswerResults extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'players:setanswerresults';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets the results of the answers';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        foreach (GameQuestionAnswer::all() as $gameQuestionAnswer) {
            if (is_null($gameQuestionAnswer->question_answer_id)) {
                continue;
            }
            if ($gameQuestionAnswer->question_answer_id == -1) {
                $gameQuestionAnswer->result = -1;
            } else {
                $questionAnswer = QuestionAnswer::find($gameQuestionAnswer->question_answer_id);
                if ($questionAnswer) {
                    $gameQuestionAnswer->result = $questionAnswer->correct;
                }
            }
            $gameQuestionAnswer->save();
        }
    }
}
