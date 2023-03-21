<?php

namespace App\Console\Commands\OneOff;

use App\Models\LearningMaterialTranslation;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Blob\Models\Blob;
use MicrosoftAzure\Storage\Blob\Models\BlobProperties;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
use MicrosoftAzure\Storage\Blob\Models\SetBlobPropertiesOptions;

class FixBrokenArticulateWBTs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'azure:fixbrokenarticulatewbts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fixes entrypoint names of articulate wbts';

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

        $wbts = LearningMaterialTranslation::whereNotNull('file_url')
            ->where('file_type', 'wbt')
            ->get()
            ->transform(function($translation) {
                $path = str_replace(['/laravel-file-storage-prod/','/laravel-file-storage/'], '',formatAssetURL($translation->file_url, '1.0.0'));
                $path = preg_replace('~^/~', '', $path);
                $translation->azureprefix = $path;
                $translation->isAffected = false;
                return $translation;
            });
        $listBlobsOptions = new ListBlobsOptions();
        $blobContents = [];
        do {
            $blobs = $blobClient->listBlobs($container, $listBlobsOptions);
            $this->info('Found '.count($blobs->getBlobs()).' blobs');
            foreach ($blobs->getBlobs() as $blob) {
                $name = $blob->getName();
                foreach($wbts as $wbt) {
                    if(Str::startsWith($name, $wbt->azureprefix)) {
                        if($name === $wbt->azureprefix . '/index_lms.html') {
                            $wbt->isAffected = true;
                            //$this->info($wbt->title);
                        }
                        $blobContents[$name] = $blob;
                    }
                }
            }

            $listBlobsOptions->setContinuationToken($blobs->getContinuationToken());
        } while ($blobs->getContinuationToken());

        foreach($wbts->where('isAffected', true) as $wbt) {
            $this->info($wbt->title);
            $blobClient->copyBlob($container, $wbt->azureprefix.'/index.html.bak20201022', $container, $wbt->azureprefix.'/index.html');
            $blobClient->copyBlob($container, $wbt->azureprefix.'/index.html', $container, $wbt->azureprefix.'/index_lms.html');
            $this->info('Fixed: ' . $wbt->azureprefix);
        }
    }
}
