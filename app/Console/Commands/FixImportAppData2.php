<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Question;
use Illuminate\Console\Command;

class FixImportAppData2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:appdatafix2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fixes: Imports compatible Quizapp data from a different database (mysql_import).';

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
        // IMPORT GAMES
        $this->line('Fixing Questions');
        $data = $this->getQuestions();

        $bar = $this->output->createProgressBar(count($data));
        foreach ($data as $row) {
            $new = $this->fixQuestion($row);
            $bar->advance();
        }
        $bar->finish();
        $this->line('');

        $this->info('All done!');
    }

    private function getQuestions()
    {
        // for models with relationships we need to fetch the object instead of an array
        return (new Question)->where('app_id', 13)->get();
    }

    private function fixQuestion($row)
    {
        $row->category_id = $this->getNewCategoryId($row->category_id);
        $row->save();

        return $row;
    }

    private function getNewCategoryId($oldCategoryId)
    {
        $oldCategory = (new Category)->setConnection('mysql_import')->find($oldCategoryId);
        $newCategory = Category::where('created_at', $oldCategory->created_at)->where('app_id', 13)->first();
        if (! $newCategory) {
            $this->error('Cant find category '.$oldCategory->id);
            throw new \Exception('cant find category');
        }

        return $newCategory->id;
    }
}
