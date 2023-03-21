<?php

namespace App\Jobs;

use App\Imports\UsersDeleter;
use App\Models\Import;
use App\Services\QueuePriority;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteUsers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Uses the mode with validation of users.
     */
    const MODE_NORMAL = 0;

    /**
     * Uses the mode to delete only given users.
     */
    const MODE_DELETE_ONLY = 1;

    /**
     * The number of times the job may be attempted.
     * @var int
     */
    public $tries = 1;

    private $additionalData;
    private $headers;
    private $users;
    private $mode;

    /**
     * Create a new job instance.
     *
     * @param $additionalData array Data which we need to import the users
     * @param $headers array The header association the user selected
     * @param $users array The user data
     * @param int $mode
     */
    public function __construct($additionalData, $headers, $users, $mode = self::MODE_NORMAL)
    {
        $this->additionalData = $additionalData;
        $this->headers = $headers;
        $this->users = $users;
        $this->mode = $mode;
        $this->queue = QueuePriority::LOW;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->additionalData = collect($this->additionalData)->merge(['mode' => $this->mode])->toArray();

        $importer = app(UsersDeleter::class);
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
