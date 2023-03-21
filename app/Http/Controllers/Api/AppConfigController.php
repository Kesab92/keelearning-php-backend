<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AppConfigEngine;
use Illuminate\Http\Request;
use Response;

class AppConfigController extends Controller
{
    /**
     * @param string $identifier Either a hostname or "slug:xyz" or "id:5"
     * @param AppConfigEngine $appConfigEngine
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getConfig($identifier, AppConfigEngine $appConfigEngine)
    {
        return Response::json($appConfigEngine->getConfig($identifier, user()));
    }

    public function getWebmanifest(AppConfigEngine $appConfigEngine, Request $request, $slug = null)
    {
        $appProfile = null;
        if($slug) {
            $appProfile = $appConfigEngine->getAppProfile('slug:' . $slug, null);
        }
        return Response::json($appConfigEngine->getWebmanifestFromRequest($request, $appProfile));
    }
}
