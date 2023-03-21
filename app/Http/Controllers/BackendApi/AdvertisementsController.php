<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Models\Advertisements\Advertisement;
use App\Models\Advertisements\AdvertisementPosition;
use App\Models\Tag;
use App\Services\Advertisements\AdvertisementsEngine;
use App\Services\ImageUploader;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Response;

class AdvertisementsController extends Controller
{
    const ORDER_BY = [
        'id',
        'name',
        'visible',
    ];
    const PER_PAGE = [
        50,
        100,
        200,
    ];

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:advertisements,advertisements-edit');
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, AdvertisementsEngine $advertisementsEngine)
    {
        $orderBy = $request->input('sortBy');
        if (! in_array($orderBy, self::ORDER_BY)) {
            $orderBy = self::ORDER_BY[0];
        }
        $orderDescending = $request->input('descending') === 'true';
        $page = (int) $request->input('page') ?? 1;
        $perPage = $request->input('rowsPerPage');
        if (! in_array($perPage, self::PER_PAGE)) {
            $perPage = self::PER_PAGE[0];
        }
        $search = $request->input('search');
        $tags = $request->input('tags', []);

        $advertisementsQuery = $advertisementsEngine->advertisementFilterQuery(appId(), $search, $tags, $orderBy, $orderDescending);

        $advertisementsCount = $advertisementsQuery->count();
        $advertisements = $advertisementsQuery
            ->with('translationRelation', 'positions', 'tags')
            ->offset($perPage * ($page - 1))
            ->limit($perPage)
            ->get();

        $advertisements = array_map(function ($advertisement) {
            unset($advertisement['translation_relation']);
            $advertisement['positions'] = collect($advertisement['positions'])->transform(function($position) {
                return $position['position'];
            });

            return $advertisement;
        }, $advertisements->values()->toArray());

        return response()->json([
            'count' => $advertisementsCount,
            'advertisements' => $advertisements,
        ]);
    }

    public function store(Request $request)
    {
        $advertisement = DB::transaction(function() use ($request) {
            $advertisement = new Advertisement();
            $advertisement->app_id = appId();
            $advertisement->name = $request->input('name');
            $advertisement->visible = false;
            $advertisement->setLanguage(defaultAppLanguage(appId()));
            $advertisement->description = ''; // Needed so the translation gets created
            $advertisement->save();

            return $advertisement;
        });

        return response()->json([
            'advertisement' => $advertisement,
        ]);
    }

    private function getAdvertisementResponse(Advertisement $advertisement) {
        $advertisement->load('tags', 'positions');
        $advertisement->tags->transform(function($tag) {
            return $tag->id;
        });
        $advertisement->positions->transform(function($position) {
            return $position->position;
        });
        $advertisement->translations = $advertisement->allTranslationRelations->whereIn('language', [defaultAppLanguage(), language()])->values();
        $advertisement->unsetRelation('allTranslationRelations');
        return [
            'advertisement' => $advertisement,
        ];
    }

    private function getAdvertisement($advertisementId)
    {
        $advertisement = Advertisement::findOrFail($advertisementId);
        if($advertisement->app_id !== appId()) {
            app()->abort(404);
        }
        return $advertisement;
    }

    public function show($advertisementId) {
        $advertisement = $this->getAdvertisement($advertisementId);
        return Response::json($this->getAdvertisementResponse($advertisement));
    }

    public function update($advertisementId, Request $request)
    {
        $advertisement = $this->getAdvertisement($advertisementId);
        $basicFields = ['name', 'visible', 'is_ad', 'link', 'description'];
        foreach($basicFields as $field) {
            $value = $request->input($field, null);
            if($value !== null) {
                $advertisement->setAttribute($field, $value);
            }
        }
        if($request->input('rectangle_image_url', null) !== null) {
            $advertisement->rectangle_image_url = $request->input('rectangle_image_url');
        }
        if($request->input('leaderboard_image_url', null) !== null) {
            $advertisement->leaderboard_image_url = $request->input('leaderboard_image_url');
        }
        $advertisement->save();

        if($request->input('tags', null) !== null) {
            $newTags = Tag::where('app_id', appId())
                ->whereIn('id', $request->input('tags'))
                ->pluck('id')
                ->toArray();
            $advertisement->tags()->sync($newTags);
        }
        if($request->input('positions', null) !== null) {
            $newPositions = $request->input('positions', []);
            // Create new positions
            foreach($newPositions as $newPosition) {
                if(!$advertisement->positions->where('position', $newPosition)->count()) {
                    $dbPosition = new AdvertisementPosition();
                    $dbPosition->advertisement_id = $advertisement->id;
                    $dbPosition->position = $newPosition;
                    $dbPosition->save();
                }
            }
            // Delete old positions
            foreach($advertisement->positions->whereNotIn('position', $newPositions) as $oldPosition) {
                $oldPosition->delete();
            }
        }

        return Response::json($this->getAdvertisementResponse($advertisement));
    }

    /**
     * Sets the cover image for a learning material.
     *
     * @param Request $request
     * @param $id
     * @param ImageUploader $imageUploader
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function uploadAsset($type, $advertisementId, Request $request, ImageUploader $imageUploader)
    {
        $validTypes = [
            'rectangle',
            'leaderboard',
        ];
        if(!in_array($type, $validTypes)) {
            app()->abort(400, 'Invalid asset type');
        }
        $advertisement = $this->getAdvertisement($advertisementId);

        $file = $request->file('file');
        if (! $imageUploader->validate($file)) {
            app()->abort(403);
        }

        if (!$imagePath = $imageUploader->upload($file, 'uploads/ad' . $type)) {
            app()->abort(400);
        }
        $imagePath = formatAssetURL($imagePath, '3.0.0');

        return \Response::json([
            'image' => $imagePath,
        ]);
    }

    public function deleteInformation($advertisementId)
    {
        $advertisement = $this->getAdvertisement($advertisementId);
        return Response::json([
            'dependencies' => $advertisement->safeRemoveDependees(),
            'blockers' => $advertisement->getBlockingDependees(),
        ]);
    }

    public function delete($advertisementId) {
        $advertisement = $this->getAdvertisement($advertisementId);

        $result = $advertisement->safeRemove();

        if($result->isSuccessful()) {
            return Response::json([], 204);
        } else {
            return Response::json($result->getMessages(), 400);
        }
    }
}
