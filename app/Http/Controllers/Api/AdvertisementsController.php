<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Advertisements\Advertisement;
use App\Models\Like;
use App\Models\News;
use App\Services\LikesEngine;
use Illuminate\Http\Request;
use Response;

class AdvertisementsController extends Controller
{
    /**
     * Returns a list of all advertisements.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function advertisements(Request $request)
    {
        $advertisements = Advertisement::query()->with('positions');
        if(user()) {
            // We only preload the translation relation here, because there is a bug when the user isn't logged in
            $advertisements = $advertisements
                ->visibleToUser(user())
                ->with('translationRelation');
        } else {
            $appId = $request->input('appId', 0);
            $advertisements = $advertisements->public($appId);
        }


        $advertisements = $advertisements->get();
        $advertisements = $advertisements->map(function($advertisement) {
                $data = $advertisement->only([
                    'id',
                    'description',
                    'link',
                    'is_ad',
                    'rectangle_image_url',
                    'leaderboard_image_url',
                ]);
                $data['positions'] = $advertisement->positions->pluck('position');
                return $data;
            })
            ->values();

        return Response::json([
            'advertisements' => $advertisements,
        ]);
    }
}
