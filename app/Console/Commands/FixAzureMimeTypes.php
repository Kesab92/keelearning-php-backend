<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Blob\Models\BlobProperties;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
use MicrosoftAzure\Storage\Blob\Models\SetBlobPropertiesOptions;

class FixAzureMimeTypes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'azure:fixmimetypes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fixes mimetypes of azure blob storage uploads';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $container = env('AZURE_STORAGE_CONTAINER');
        $connectionString = 'DefaultEndpointsProtocol=https;AccountName='.env('AZURE_STORAGE_NAME').';AccountKey='.env('AZURE_STORAGE_KEY');
        $blobClient = BlobRestProxy::createBlobService($connectionString);

        $listBlobsOptions = new ListBlobsOptions();
        do {
            $blobs = $blobClient->listBlobs($container, $listBlobsOptions);
            $this->info('Found '.count($blobs->getBlobs()).' blobs');
            foreach ($blobs->getBlobs() as $blob) {
                if (Str::endsWith($blob->getName(), '.pdf')) {
                    $this->info('Fix: '.$blob->getName());
                    $properties = new BlobProperties();
                    $properties->setContentType('application/pdf');
                    $setProperties = new SetBlobPropertiesOptions($properties);
                    $blobClient->setBlobProperties($container, $blob->getName(), $setProperties);
                }
            }

            $listBlobsOptions->setContinuationToken($blobs->getContinuationToken());
        } while ($blobs->getContinuationToken());
    }
}
