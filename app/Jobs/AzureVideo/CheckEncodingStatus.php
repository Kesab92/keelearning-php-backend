<?php

namespace App\Jobs\AzureVideo;

use App\Models\AzureVideo;
use App\Services\AzureVideo\AzureVideoEngine;
use App\Services\QueuePriority;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Sentry;

class CheckEncodingStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const FINISHING_STATES = [
        'Canceled',
        'Error',
        'Finished',
    ];

    private $azureVideo;

    /**
     * The number of times the job may be attempted.
     * @var int
     */
    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @param AzureVideo $azureVideo
     */
    public function __construct(AzureVideo $azureVideo)
    {
        $this->queue = QueuePriority::HIGH;
        $this->azureVideo = $azureVideo;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $azureVideoEngine = app(AzureVideoEngine::class);
        $job = $azureVideoEngine->getEncodingJob($this->azureVideo);

        $this->azureVideo->status = AzureVideo::STATES[$job['properties']['state']];
        $this->azureVideo->progress = $job['properties']['outputs'][0]['progress'];

        if (!in_array($job['properties']['state'], self::FINISHING_STATES)) {
            $this->azureVideo->save();
            self::dispatch($this->azureVideo)->delay(now()->addSeconds(3));
            return;
        }

        if ($job['properties']['state'] != 'Finished') {
            $this->azureVideo->save();
            Sentry::captureMessage('Job '.$this->azureVideo->job_name.' ended with status '.$job['properties']['state']);
            return;
        }

        $this->azureVideo->finished_at = $job['properties']['endTime'];
        $this->azureVideo->streaming_url = $azureVideoEngine->getStreamingURL($this->azureVideo);
        $this->azureVideo->save();
    }

    public function tags()
    {
        return ['appid:'.$this->azureVideo->app_id];
    }
}
