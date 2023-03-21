<?php

namespace App\Console\Commands;

use App\Models\Test;
use App\Models\TestSubmissionAnswer;
use Illuminate\Console\Command;

class PurgeTestSubmissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tests:purge-submissions {testId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purges all submissions for a given test';

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
        $test = Test::findOrFail($this->argument('testId'));
        $app = $test->app;
        $submissionCount = $test->submissions()->count();
        $this->info('Deleting ' . $submissionCount . ' submissions for test : '.$test->name.' of app : '.$app->name);
        if(!$this->confirm('Are you sure that\'s what you want to do?')) {
            $this->info('Aborting');
            return;
        }


        $submissionKeys = $test->submissions->pluck('id');
        TestSubmissionAnswer::whereIn('test_submission_id', $submissionKeys)->delete();
        $test->submissions()->delete();

        $this->info('Purging successful.');
    }
}
