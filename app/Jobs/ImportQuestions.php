<?php

namespace App\Jobs;

use App\Imports\Importer;
use App\Imports\QuestionsBooleanImporter;
use App\Imports\QuestionsIndexCardsImporter;
use App\Imports\QuestionsMultipleChoiceImporter;
use App\Imports\QuestionsSingleChoiceImporter;
use App\Models\Import;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportQuestions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     * @var int
     */
    public $tries = 1;

    private $additionalData;
    private $headers;
    private $questions;

    /**
     * Create a new job instance.
     *
     * @param $additionalData array Data which we need to import the users
     * @param $headers array The header association the user selected
     * @param $questions
     */
    public function __construct($additionalData, $headers, $questions)
    {
        $this->additionalData = $additionalData;
        $this->headers = $headers;
        $this->questions = $questions;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        /** @var Importer $importer */
        $importer = null;
        switch ($this->additionalData['type']) {
            case 'singlechoice':
                $importer = app(QuestionsSingleChoiceImporter::class);
                break;
            case 'multiplechoice':
                $importer = app(QuestionsMultipleChoiceImporter::class);
                break;
            case 'boolean':
                $importer = app(QuestionsBooleanImporter::class);
                break;
            case 'indexcards':
                $importer = app(QuestionsIndexCardsImporter::class);
                break;
            default:
                $importer = null;
                break;
        }
        $importer->import($this->additionalData, $this->headers, $this->questions);
    }

    /**
     * The job failed to process.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function failed(\Exception $exception)
    {
        report($exception);
        $import = Import::find($this->additionalData['importId']);
        $import->status = Import::STATUS_FAILED;
        $import->save();
    }
}
