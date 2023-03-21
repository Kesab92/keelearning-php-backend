<?php

namespace App\Console\Commands;

use App\Models\GameQuestionAnswer;
use Illuminate\Console\Command;

class Gametest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:test';

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
        $day = '2017-02-07';
        $this->info($answers = GameQuestionAnswer::where('created_at', '>=', $day.' 00:00:00')
                                                 ->where('created_at', '<=', $day.' 23:59:59')->count());
        $answers = GameQuestionAnswer::where('created_at', '>=', $day.' 00:00:00')
            ->where('created_at', '<=', $day.' 23:59:59')
            ->whereNull('result')
            ->get();
        foreach ($answers as $answer) {
            $this->info($answer->created_at.'('.$answer->gameQuestion->gameRound->game_id.')');
        }
    }
}
