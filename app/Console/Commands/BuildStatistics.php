<?php

namespace App\Console\Commands;

use App\Models\Question;
use App\Models\Tag;
use App\Stats\QuestionCorrectByPlayers;
use App\Stats\QuestionWrongByPlayers;
use Excel;
use Illuminate\Console\Command;

class BuildStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deprecated-not-working:stats:build {appId} {tag}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates statistics for given tag of app';

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
        $this->info('Building statistics for app: '.$this->argument('appId').' with tag: '.$this->argument('tag'));

        // Query data
        $this->info('Querying data...');
        $tag = Tag::where('label', $this->argument('tag'))
            ->where('app_id', $this->argument('appId'))
            ->first();

        $userIds = $tag->users->map(function ($user) {
            return $user->id;
        });

        $questions = Question::where('app_id', $this->argument('appId'))->get();
        $questions->map(function ($question) use ($userIds) {
            $correct = (new QuestionCorrectByPlayers($question->id, $userIds))->fetch();
            $wrong = (new QuestionWrongByPlayers($question->id, $userIds))->fetch();

            $question->stats = [
                'correct' => $correct,
                'wrong' => $wrong,
            ];
        });
        $this->info('Found '.count($questions).' entries');

        // Create output
        $this->info('Building export...');
        Excel::create('statistiken-fragen-'.$this->argument('tag'), function ($excel) use ($questions) {
            $excel->sheet('Sheetname', function ($sheet) use ($questions) {
                $sheet->loadView('stats.quiz.csv.questions')
                      ->with('questions', $questions);
            });
        })->store('xlsx');
        $this->info('Building statistics finished');
    }
}
