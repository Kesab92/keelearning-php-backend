<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FcmToken;
use Illuminate\Http\Request;
use Response;

class FCMController extends Controller
{
    public function addFCMToken(Request $request)
    {
        FcmToken::unguard();
        FcmToken::updateOrCreate(
            [
                'user_id' => user()->id,
                'token' => $request->input('token'),
            ],
            [
                'app_store_id' => $request->input('appStoreId'),
                'platform' => $request->input('platform'),
                'model' => $request->input('model'),
            ]
        );
        FcmToken::reguard();
        return Response::json([], 200);
    }
}
