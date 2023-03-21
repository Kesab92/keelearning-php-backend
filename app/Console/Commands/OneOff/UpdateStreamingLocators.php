<?php

namespace App\Console\Commands\OneOff;

use App\Models\App;
use App\Models\AzureVideo;
use App\Services\AzureVideo\AzureVideoEngine;
use Illuminate\Console\Command;

class UpdateStreamingLocators extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:updatestreaminglocators';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the streaming locators of all azure videos';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $videos = AzureVideo::all();
        $azureVideoEngine = app(AzureVideoEngine::class);
        foreach($videos as $video) {
            try {
                $encodedAsset = $azureVideoEngine->getAsset($video->output_asset_id);

                $streamingURL = $azureVideoEngine->getStreamingURL($encodedAsset);
                $streamingURL = preg_replace('~^http://~', 'https://', $streamingURL);
                $video->streaming_url = $streamingURL;
                $video->save();
            } catch (\Exception $e) {
                report($e);
                $this->error($e->getMessage());
            }
        }
    }
}
