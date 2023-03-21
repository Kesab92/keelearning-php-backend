<?php

namespace App\Console\Commands;

use App\Models\TestSubmissionAnswer;
use Illuminate\Console\Command;

class MigrateTestSubmissionAnswerQuestionIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:testquestionids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrates the Question IDs for Test Submission Answers';

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
        $testSubmissionAnswers = TestSubmissionAnswer::where('question_id', 0)
            ->whereNotNull('test_question_id')
            ->with('testQuestion');
        $this->line('Migrating TestSubmissionAnswer question_id');
        $bar = $this->output->createProgressBar($testSubmissionAnswers->count());
        $testSubmissionAnswers->chunkById(100, function ($testSubmissionAnswers) use ($bar) {
            foreach ($testSubmissionAnswers as $testSubmissionAnswer) {
                if ($testSubmissionAnswer->testQuestion) {
                    $testSubmissionAnswer->question_id = $testSubmissionAnswer->testQuestion->question_id;
                    $testSubmissionAnswer->save();
                }
                $bar->advance();
            }
        });
        $bar->finish();
        $this->line('');
        $this->line('Migration done');
    }
}
