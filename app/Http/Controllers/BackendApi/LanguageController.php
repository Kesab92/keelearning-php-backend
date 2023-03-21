<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;

class LanguageController extends Controller
{
    /**
     * Returns the apps languages and the currently active language
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getLanguageConfig()
    {
        return \Response::json([
            'activeLanguage' => language(),
            'languages' => appLanguages(),
        ]);
    }
}
