<?php

namespace App\Jobs;

use App\Imports\UsersImporter;
use App\Models\Import;
use App\Services\QueuePriority;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportUsers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     * @var int
     */
    public $tries = 1;

    private $additionalData;
    private $headers;
    private $users;

    /**
     * Create a new job instance.
     *
     * @param $additionalData array Data which we need to import the users
     * @param $headers array The header association the user selected
     * @param $users array The user data
     */
    public function __construct($additionalData, $headers, $users)
    {
        $this->additionalData = $additionalData;
        $this->headers = $headers;
        $this->users = $users;
        $this->queue = QueuePriority::HIGH;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $importer = app(UsersImporter::class);
        $importer->import($this->additionalData, $this->headers, $this->users);
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
