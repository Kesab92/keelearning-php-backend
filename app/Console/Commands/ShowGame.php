<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Models\Question;
use App\Services\AppSettings;
use App\Services\Terminator;
use Illuminate\Console\Command;

class ShowGame extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:show {gameid}';

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
        $game = Game::with('gameRounds.gameQuestions.gameQuestionAnswers')->find($this->argument('gameid'));
        try {
            if ($game->status > 0 && Terminator::isGameTooOld($game)) {
                $this->error('Game is too old');
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        if ($game->status == -1) {
            $this->error('Game was canceled');
        }
        foreach ($game->gameRounds as $roundIdx => $round) {
            $this->info('Round '.$roundIdx.', '.$round->category->name);
            foreach ($round->gameQuestions as $question) {
                $this->info('    '.$question->question->title);
                foreach ($question->gameQuestionAnswers as $answer) {
                    if ($answer->result == -1 || $answer->question_answer_id == -1) {
                        $this->error('        '.$answer->user->username.': Out of time ('.$answer->created_at.')');
                    } elseif (is_null($answer->result)) {
                        $this->error('        '.$answer->user->username.': Answering... ('.$answer->created_at.')');
                    } elseif ($answer->result == 1) {
                        if ($question->question->type == Question::TYPE_MULTIPLE_CHOICE) {
                            $this->info('        '.$answer->user->username.': '.implode(',', $answer->multiple).' ('.$answer->created_at.')');
                        } else {
                            $this->info('        '.$answer->user->username.': '.$answer->questionAnswer->content.' ('.$answer->created_at.')');
                        }
                    } else {
                        if ($question->question->type == Question::TYPE_MULTIPLE_CHOICE) {
                            $this->error('        '.$answer->user->username.': WRONG '.implode(',', $answer->multiple).' ('.$answer->created_at.')');
                        } else {
                            $this->error('        '.$answer->user->username.': WRONG '.$answer->questionAnswer->content.' ('.$answer->created_at.')');
                        }
                    }
                }
            }
        }
        //$this->info($game->toArray());
    }
}
