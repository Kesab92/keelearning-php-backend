<?php

namespace App\Jobs;

use App\Push\Deepstream;
use App\Services\QueuePriority;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendDeepstreamEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $event;
    private $data;
    private $app;

    /**
     * The number of times the job may be attempted.
     * @var int
     */
    public $tries = 1;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($event, $data, $app)
    {
        $this->queue = QueuePriority::HIGH;
        $this->event = $event;
        $this->data = $data;
        $this->app = $app;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Deepstream::getClient()->emitEvent($this->event, $this->data);
    }

    public function tags()
    {
        return ['internal-appid:'.$this->app->id];
    }
}
