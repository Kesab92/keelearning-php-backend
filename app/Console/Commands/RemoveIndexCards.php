<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Test;
use App\Models\TestSubmissionAnswer;
use Illuminate\Console\Command;

class RemoveIndexCards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'indexcards:remove {categoryId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes all index cards by category id';

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
     * @return int
     */
    public function handle()
    {
        $category = Category::findOrFail($this->argument('categoryId'));
        $app = $category->app;
        $cardCount = $category->indexCards()->count();
        $this->info('Deleting ' . $cardCount . ' index cards of app: '.$app->name);
        if(!$this->confirm('Are you sure that\'s what you want to do?')) {
            $this->info('Aborting');
            return;
        }

        foreach($category->indexCards as $indexCard) {
            $result = $indexCard->safeRemove();
            if(!$result->isSuccessful()) {
                $this->error('Couldnt delete ' . $indexCard->id);
                $this->error(json_encode($result->getMessages()));
                throw new \Exception('removal failed');
            }
        }

        $this->info('Removed all cards successfully.');
    }
}
