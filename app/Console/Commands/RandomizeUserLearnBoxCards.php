<?php

namespace App\Console\Commands;

use App\Models\LearnBoxCard;
use Illuminate\Console\Command;

class RandomizeUserLearnBoxCards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'randomizeuserlearnboxcards {user}';

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
        $userId = $this->argument('user');
        if ($userId != 4805 && $userId != 4580 && $userId != 4579 && $userId != 4246) {
            $this->error('Invalid user');

            return;
        }
        $cards = LearnBoxCard::where('user_id', $userId)->get();
        foreach ($cards as $card) {
            $card->box = random_int(0, 4);
            $card->save();
        }
    }
}
