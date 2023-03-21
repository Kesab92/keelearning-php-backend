<?php

namespace App\Console\Commands;

use App\Models\Game;
use Illuminate\Console\Command;

class CheckLastGames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:check {last=10}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks how many answers have been given for the last 10 finished games';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $games = Game::where('status', 0)->orderBy('id', 'desc')->take(intval($this->argument('last')))->get();

        $data = [];
        $day = date('Y-m-d');
        while ($day >= date('Y-m-d', strtotime('-10 days'))) {
            if ($day == null) {
                $filepath = storage_path('logs/access-log-'.date('Y-m-d').'.log');
            } else {
                $filepath = storage_path('logs/access-log-'.$day.'.log');
            }
            $handle = @fopen($filepath, 'r');
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    $data[] = $line;
                }

                fclose($handle);
            } else {
                $this->error($filepath.' does not exist.');
            }
            $day = date('Y-m-d', strtotime('-1 day', strtotime($day)));
        }
        foreach ($games as $game) {
            $gameQuestionGETs = 0;
            foreach ($data as $entry) {
                if (strpos($entry, ' GET ') !== false) {
                    if (strpos($entry, '/api/v1/games/'.$game->id.'/question') !== false) {
                        $gameQuestionGETs++;
                    }
                }
            }
            $this->info('Game '.$game->id.': '.$gameQuestionGETs.' / '.(3 * 5 * 2));
        }
    }
}
