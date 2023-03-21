<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Models\AzureVideo;
use Exception;
use Illuminate\Http\JsonResponse;
use Response;

class AzureVideoController extends Controller
{
    /**
     * Returns the status of the given video.
     *
     * @param $azureVideoId
     * @return JsonResponse
     * @throws Exception
     */
    public function status($azureVideoId)
    {
        $azureVideo = AzureVideo::where('app_id', appId())
            ->where('id', $azureVideoId)
            ->firstOrFail();

        $subtitles = $azureVideo->subtitles()
            ->where('language', $azureVideo->subtitles_language)
            ->get();
        return Response::json([
            'azureVideo' => $azureVideo,
            'subtitles' => $subtitles,
        ]);
    }
}
