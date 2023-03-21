<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Models\App;
use Exception;
use Illuminate\Http\JsonResponse;

class TemplateInheritanceController extends Controller
{
    /**
     * Returns the apps that this app can inherit templates to
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function getChildApps()
    {
        $templateInheritanceApps = App::find(appId())
            ->templateInheritanceChildren()
            ->with('profiles.settings')
            ->get()
            ->transform(function(App $app) {
                return [
                    'id' => $app->id,
                    'app_name' => $app->app_name,
                    'default_language' => defaultAppLanguage($app->id),
                ];
            });

        return response()->json([
            'apps' => $templateInheritanceApps,
        ]);
    }
}
