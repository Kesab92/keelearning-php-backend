<?php

namespace App\Services\AzureVideo;

use App\Jobs\AzureVideo\CheckEncodingStatus;
use App\Jobs\AzureVideo\CheckSubtitleStatus;
use App\Models\AzureVideo;
use App\Models\AzureVideoSubtitle;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\File;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use League\Flysystem\AzureBlobStorage\AzureBlobStorageAdapter;
use League\Flysystem\Filesystem;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;

class AzureVideoEngine
{
    // https://docs.microsoft.com/en-us/azure/media-services/latest/analyze-video-audio-files-concept#built-in-presets
    const SUBTITLE_LANGUAGES = [
        'ar-AE',
        'ar-BH',
        'ar-EG',
        'ar-IL',
        'ar-IQ',
        'ar-JO',
        'ar-KW',
        'ar-LB',
        'ar-OM',
        'ar-PS',
        'ar-QA',
        'ar-SA',
        'ar-SY',
        'cs-CZ',
        'da-DK',
        'de-DE',
        'en-AU',
        'en-GB',
        'en-US',
        'es-ES',
        'es-MX',
        'fi-FI',
        'fr-CA',
        'fr-FR',
        'hi-IN',
        'it-IT',
        'ja-JP',
        'ko-KR',
        'nb-NO',
        'nl-NL',
        'pl-PL',
        'pt-BR',
        'ru-RU',
        'sv-SE',
        'th-TH',
        'tr-TR',
        'zh-CN',
        'zh-HK',
    ];

    private $accessToken;
    private $config;

    public function __construct()
    {
        $this->config = [
            'aadClientId'            => env('AZURE_MEDIA_V3_AADCLIENTID'),
            'aadClientSecret'        => env('AZURE_MEDIA_V3_AADCLIENTSECRET'),
            'aadTenantDomain'        => env('AZURE_MEDIA_V3_AADTENANTDOMAIN'),
            'accountName'            => env('AZURE_MEDIA_V3_ACCOUNTNAME'),
            'api-version'            => env('AZURE_MEDIA_V3_APIVERSION'),
            'arm-resource'           => env('AZURE_MEDIA_V3_ARMRESOURCE'),
            'baseUrl'                => env('AZURE_MEDIA_V3_BASEURL'),
            'encodeTransformName'    => env('AZURE_MEDIA_V3_ENCODETRANSFORMNAME'),
            'resourceGroup'          => env('AZURE_MEDIA_V3_RESOURCEGROUP'),
            'storageAccountKey'      => env('AZURE_MEDIA_V3_STORAGEACCOUNTKEY'),
            'storageAccountName'     => env('AZURE_MEDIA_V3_STORAGEACCOUNTNAME'),
            'streamingEndpointHost'  => env('AZURE_MEDIA_V3_STREAMINGENDPOINTHOST'),
            'streamingPolicy'        => env('AZURE_MEDIA_V3_STREAMINGPOLICY'),
            'subscriptionId'         => env('AZURE_MEDIA_V3_SUBSCRIPTIONID'),
            'subtitleTransformName'  => env('AZURE_MEDIA_V3_SUBTITLETRANSFORMNAME'),
        ];
    }

    public function uploadVideo(int $appId, File $file): AzureVideo
    {
        set_time_limit(0);
        $assetName = 'kl_azure_video_'.uniqid().'_'.Str::limit(Str::slug($file->getFilename()), 100, '');

        // 1. Create Input Asset
        $inputAsset = $this->createAsset('input_'.$assetName, [
            'description' => 'Source file of upload '.$file->getFilename(),
        ]);

        // 2. Upload Video into it
        // every asset has a storage blob named `asset-${assetId}`,
        // which is returned in `asset.properties.container`,
        // but unfortunately not on creation
        $this->uploadFile('asset-'.$inputAsset['properties']['assetId'], $file);

        // 3. Create Output Asset
        $outputAsset = $this->createAsset('encoded_'.$assetName, [
            'description' => 'Encoded files for upload ' . $file->getFilename(),
        ]);

        // 4. Start Encoding Job
        $encodingJob = $this->createJob('job_encode_'.$assetName, $this->config['encodeTransformName'], [
            'input' => [
                '@odata.type' => '#Microsoft.Media.JobInputAsset',
                'assetName'   => $inputAsset['name'],
            ],
            'outputs' => [[
                '@odata.type' => '#Microsoft.Media.JobOutputAsset',
                'assetName'   => $outputAsset['name'],
            ]],
        ]);

        // 5. create azure video
        $azureVideo = new AzureVideo;
        $azureVideo->app_id = $appId;
        $azureVideo->progress = 0;
        $azureVideo->status = AzureVideo::STATES['Queued'];
        $azureVideo->job_id = $encodingJob['id'];
        $azureVideo->job_name = $encodingJob['name'];
        $azureVideo->input_asset_id = $inputAsset['properties']['assetId'];
        $azureVideo->input_asset_name = $inputAsset['name'];
        $azureVideo->output_asset_id = $outputAsset['properties']['assetId'];
        $azureVideo->output_asset_name = $outputAsset['name'];
        $azureVideo->save();

        // 6. start laravel job to monitor progress
        CheckEncodingStatus::dispatch($azureVideo);

        return $azureVideo;
    }

    public function setSubtitleLanguage(AzureVideo $azureVideo, ?string $languageCode): ?AzureVideoSubtitle
    {
        if ($languageCode && !in_array($languageCode, self::SUBTITLE_LANGUAGES)) {
            throw new Exception('This language is not supported by Azure!');
        }

        $azureVideo->subtitles_language = $languageCode;
        $azureVideo->save();

        if (!$languageCode) {
            return null;
        }

        // 0. check for already existing subtitle entry
        $azureVideoSubtitle = $azureVideo->subtitles()
            ->where('language', $languageCode)
            ->first();
        if ($azureVideoSubtitle) {
            return $azureVideoSubtitle;
        }

        // 1. create asset for subtitles
        // we use an own asset for each subtitle language,
        // since the transform won't allow us to set a target file name/prefix
        $assetName = $azureVideo->input_asset_name;
        if (Str::startsWith('input_', $assetName)) {
            $assetName = Str::replaceFirst('input_', '', $assetName);
        }
        $subtitlesAsset = $this->createAsset('subtitles_'.$languageCode.'_'.$assetName, [
            'description' => 'Subtitles in language '.$languageCode,
        ]);

        // 2. start subtitles generation job
        $subtitlesJob = $this->createJob('job_subtitles_'.$languageCode.'_'.$azureVideo->input_asset_name, $this->config['subtitleTransformName'], [
            'input' => [
                '@odata.type' => '#Microsoft.Media.JobInputAsset',
                'assetName'   => $azureVideo->input_asset_name,
            ],
            'outputs' => [[
                '@odata.type' => '#Microsoft.Media.JobOutputAsset',
                'assetName'   => $subtitlesAsset['name'],
                'presetOverride' => [
                    '@odata.type'   => '#Microsoft.Media.AudioAnalyzerPreset',
                    'audioLanguage' => $languageCode,
                    'mode'          => 'Basic',
                ],
            ]],
        ]);

        // 3. create subtitles entry
        $azureVideoSubtitle = new AzureVideoSubtitle;
        $azureVideoSubtitle->azure_video_output_asset_id = $azureVideo->output_asset_id;
        $azureVideoSubtitle->language = $languageCode;
        $azureVideoSubtitle->progress = 0;
        $azureVideoSubtitle->status = AzureVideo::STATES['Queued'];
        $azureVideoSubtitle->job_name = $subtitlesJob['name'];
        $azureVideoSubtitle->asset_name = $subtitlesAsset['name'];
        $azureVideoSubtitle->save();

        // 4. start laravel job to monitor progress
        CheckSubtitleStatus::dispatch($azureVideoSubtitle);

        return $azureVideoSubtitle;
    }

    public function isAVideo($mimeType): bool
    {
        if (is_null($mimeType)) {
            return false;
        }
        $validExtraMimeTypes = [
            'application/mxf',
            'application/x-matroska',
        ];

        // We accept all video types
        if (Str::startsWith($mimeType, 'video/')) {
            return true;
        }
        if (in_array($mimeType, $validExtraMimeTypes)) {
            return true;
        }

        return false;
    }

    public function getEncodingJob(AzureVideo $azureVideo): array
    {
        $response = $this->httpClient()
            ->get($this->makeUrl('/transforms/'.$this->config['encodeTransformName'].'/jobs/'.$azureVideo->job_name));
        if (!$response->successful()) {
            throw new Exception('Could not fetch encoding job!');
        }
        return $response->json();
    }

    public function getSubtitleJob(AzureVideoSubtitle $azureVideoSubtitle): array
    {
        $response = $this->httpClient()
            ->get($this->makeUrl('/transforms/'.$this->config['subtitleTransformName'].'/jobs/'.$azureVideoSubtitle->job_name));
        if (!$response->successful()) {
            throw new Exception('Could not fetch encoding job!');
        }
        return $response->json();
    }

    public function getStreamingURL(AzureVideo $azureVideo): string
    {
        $response = $this->httpClient()
            ->put($this->makeUrl('/streamingLocators/streaminglocator-'.$azureVideo->output_asset_name), [
                'properties' => [
                    'streamingPolicyName' => $this->config['streamingPolicy'],
                    'assetName' => $azureVideo->output_asset_name,
                ],
            ]);
        if (!$response->successful()) {
            throw new Exception('Could not create Streaming Locator!');
        }
        // this is a POST request for some reason
        $response = $this->httpClient()
            ->post($this->makeUrl('/streamingLocators/streaminglocator-'.$azureVideo->output_asset_name.'/listPaths'));
        if (!$response->successful()) {
            throw new Exception('Could not fetch paths!');
        }
        $streamingPaths = collect($response->json()['streamingPaths']);
        return 'https://'.$this->config['streamingEndpointHost'].$streamingPaths->firstWhere('streamingProtocol', 'SmoothStreaming')['paths'][0];
    }

    public function getSubtitleUrl(AzureVideoSubtitle $azureVideoSubtitle): string
    {
        $response = $this->httpClient()
            ->put($this->makeUrl('/streamingLocators/streaminglocator-'.$azureVideoSubtitle->asset_name), [
                'properties' => [
                    'streamingPolicyName' => $this->config['streamingPolicy'],
                    'assetName' => $azureVideoSubtitle->asset_name,
                ],
            ]);
        if (!$response->successful()) {
            throw new Exception('Could not create Streaming Locator!');
        }
        // this is a POST request for some reason
        $response = $this->httpClient()
            ->post($this->makeUrl('/streamingLocators/streaminglocator-'.$azureVideoSubtitle->asset_name.'/listPaths'));
        if (!$response->successful()) {
            throw new Exception('Could not fetch paths!');
        }
        $downloadPath = collect($response->json()['downloadPaths'])->first(function($path) {
            return Str::endsWith($path, '.vtt');
        });
        return 'https://'.$this->config['streamingEndpointHost'].$downloadPath;
    }

    private function setAccessToken(): void
    {
        $response = Http::retry(5, 1000)
            ->asForm()
            ->post('https://login.microsoftonline.com/' . $this->config['aadTenantDomain'] . '/oauth2/token', [
                    'client_id'     => $this->config['aadClientId'],
                    'client_secret' => $this->config['aadClientSecret'],
                    'grant_type'    => 'client_credentials',
                    'resource'      => $this->config['arm-resource'],
                ]);
        if (!$response->successful()) {
            throw new Exception('Could not fetch Azure Access Token!');
        }
        $this->accessToken = $response->json()['access_token'];
    }

    private function httpClient(): PendingRequest
    {
        if (!$this->accessToken) {
            $this->setAccessToken();
        }
        return Http::withToken($this->accessToken)
            ->retry(5, 1000, function ($exception) {
                return $exception instanceof ConnectionException;
            });
    }

    private function makeUrl(string $path): string
    {
        return 'https://'.$this->config['baseUrl'].'/subscriptions/'.$this->config['subscriptionId'].'/resourceGroups/'.$this->config['resourceGroup'].'/providers/Microsoft.Media/mediaServices/'.$this->config['accountName'].$path.'?api-version='.$this->config['api-version'];
    }

    private function uploadFile(string $blobStorageContainer, File $file, ?string $filename = null): void
    {
        $filesystem = $this->getAzureStorageFilesystem($blobStorageContainer);
        $inputFileStream = fopen($file->path(), 'r+');
        $filesystem->writeStream($filename ?: $file->getFilename(), $inputFileStream);
        if (is_resource($inputFileStream)) {
            fclose($inputFileStream);
        }
    }

    public function getAzureStorageFilesystem(string $blobStorageContainer): Filesystem
    {
        $azureStorageClient = BlobRestProxy::createBlobService('DefaultEndpointsProtocol=https;AccountName='.$this->config['storageAccountName'].';AccountKey='.$this->config['storageAccountKey'].';EndpointSuffix=core.windows.net');
        $azureStorageAdapter = new AzureBlobStorageAdapter($azureStorageClient, $blobStorageContainer);
        return new Filesystem($azureStorageAdapter);
    }

    private function createAsset(string $assetName, array $assetProperties = []): array
    {
        $response = $this->httpClient()
            ->put($this->makeUrl('/assets/'.$assetName), [
                'properties' => $assetProperties,
            ]);
        if (!$response->successful()) {
            throw new Exception('Asset creation failed!');
        }
        return $response->json();
    }

    private function createJob(string $jobName, string $transformName, array $jobProperties): array
    {
        $response = $this->httpClient()
            ->put($this->makeUrl('/transforms/'.$transformName.'/jobs/'.$jobName),
            [
                'properties' => $jobProperties,
            ]);
        if (!$response->successful()) {
            throw new Exception('Job creation failed!');
        }
        return $response->json();
    }

    public function getActiveSubtitles(array $azureVideoIds): Collection
    {
        return DB::table('azure_video_subtitles')
            ->select('azure_video_subtitles.*')
            ->join('azure_videos', function ($join) {
                $join->on('azure_video_subtitles.azure_video_output_asset_id', '=', 'azure_videos.output_asset_id')
                    ->on('azure_video_subtitles.language', '=', 'azure_videos.subtitles_language');
            })
            ->whereIn('azure_videos.id', $azureVideoIds)
            ->whereNotNull('azure_video_subtitles.streaming_url')
            ->get();
    }
}
