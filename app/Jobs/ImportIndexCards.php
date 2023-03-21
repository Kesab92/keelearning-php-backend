<?php

namespace App\Jobs;

use App\Imports\Importer;
use App\Imports\IndexcardsImporter;
use App\Models\Import;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportIndexCards implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     * @var int
     */
    public $tries = 1;

    private $additionalData;
    private $headers;
    private $indexcards;

    /**
     * Create a new job instance.
     *
     * @param $additionalData array Data which we need to import the users
     * @param $headers array The header association the user selected
     * @param $indexcards
     */
    public function __construct($additionalData, $headers, $indexcards)
    {
        $this->additionalData = $additionalData;
        $this->headers = $headers;
        $this->indexcards = $indexcards;
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
        $importer = app(IndexcardsImporter::class);
        $importer->import($this->additionalData, $this->headers, $this->indexcards);
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
