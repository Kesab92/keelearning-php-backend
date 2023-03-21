<?php

namespace App\Jobs\AzureVideo;

use App\Models\AzureVideo;
use App\Models\AzureVideoSubtitle;
use App\Services\AzureVideo\AzureVideoEngine;
use App\Services\QueuePriority;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Sentry;

class CheckSubtitleStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const FINISHING_STATES = [
        'Canceled',
        'Error',
        'Finished',
    ];

    private $azureVideoSubtitle;

    /**
     * The number of times the job may be attempted.
     * @var int
     */
    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @param AzureVideoSubtitle $azureVideoSubtitle
     */
    public function __construct(AzureVideoSubtitle $azureVideoSubtitle)
    {
        $this->queue = QueuePriority::HIGH;
        $this->azureVideoSubtitle = $azureVideoSubtitle;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $azureVideoEngine = app(AzureVideoEngine::class);
        $job = $azureVideoEngine->getSubtitleJob($this->azureVideoSubtitle);

        $this->azureVideoSubtitle->status = AzureVideo::STATES[$job['properties']['state']];
        $this->azureVideoSubtitle->progress = $job['properties']['outputs'][0]['progress'];

        if (!in_array($job['properties']['state'], self::FINISHING_STATES)) {
            $this->azureVideoSubtitle->save();
            self::dispatch($this->azureVideoSubtitle)->delay(now()->addSeconds(3));
            return;
        }

        if ($job['properties']['state'] != 'Finished') {
            $this->azureVideoSubtitle->save();
            Sentry::captureMessage('Job '.$this->azureVideoSubtitle->job_name.' ended with status '.$job['properties']['state']);
            return;
        }

        $this->azureVideoSubtitle->finished_at = $job['properties']['endTime'];
        $this->azureVideoSubtitle->streaming_url = $azureVideoEngine->getSubtitleUrl($this->azureVideoSubtitle);
        $this->azureVideoSubtitle->save();
    }

    public function tags()
    {
        return ['appid:'.$this->azureVideoSubtitle->app_id];
    }
}
