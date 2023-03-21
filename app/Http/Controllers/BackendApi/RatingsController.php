<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Models\AppRating;
use Response;

class RatingsController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:,settings-ratings');
    }

    /**
     * Returns all Ratings based on the app.
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRatings()
    {
        $ratings = AppRating::with('user.tags')
            ->whereHas('user', function ($query) {
                return $query->where('users.app_id', appId());
            })
            ->get()
            ->map(function ($rating) {
                return [
                    'rating' => $rating->rating,
                    'tags' => $rating->user->tags->pluck('label'),
                    'updated_at' => $rating->updated_at->toDateTimeString(),
                ];
            });

        return Response::json([
            'success' => true,
            'data' => $ratings,
        ]);
    }
}
