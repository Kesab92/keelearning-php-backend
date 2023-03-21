<?php

namespace App\Console\Commands;

use App\Models\App;
use App\Models\Category;
use App\Models\Game;
use App\Models\Question;
use App\Models\Tag;
use Illuminate\Console\Command;

class MigrateWohndarlehen extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:wohndarlehen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrates all questions from wohndarlehen to wüstenrot and deletes all wohndarlehen games';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->migrateTAGs();
        $this->migrateCategories();
        $this->migrateQuestions();
        $this->removeGames();
    }

    private function migrateTAGs()
    {
        Tag::where('app_id', App::ID_WOHNDARLEHEN)
            ->update(['app_id' => App::ID_WUESTENROT]);
    }

    private function migrateCategories()
    {
        Category::where('app_id', App::ID_WOHNDARLEHEN)
            ->update(['app_id' => App::ID_WUESTENROT]);
    }

    private function migrateQuestions()
    {
        Question::where('app_id', App::ID_WOHNDARLEHEN)
            ->update(['app_id' => App::ID_WUESTENROT]);
    }

    private function removeGames()
    {
        $games = Game::where('app_id', App::ID_WUESTENROT)->get();
        $bar = $this->output->createProgressBar(count($games));
        foreach ($games as $game) {
            $game->safeRemove();
            $bar->advance();
        }
        $bar->finish();
    }
}
