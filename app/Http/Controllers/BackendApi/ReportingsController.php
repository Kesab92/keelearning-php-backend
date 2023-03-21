<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Reporting;
use App\Models\Tag;
use App\Services\PermissionEngine;
use App\Services\ReportingEngine;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Response;

class ReportingsController extends Controller
{
    const ORDER_BY = [
        'id',
    ];
    const PER_PAGE = [
        50,
        100,
        200,
    ];
    /**
     * @var ReportingEngine
     */
    private ReportingEngine $reportingEngine;

    public function __construct(ReportingEngine $reportingEngine)
    {
        parent::__construct();
        $this->reportingEngine = $reportingEngine;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function index(Request $request)
    {
        $orderBy = $request->input('sortBy');
        if (!in_array($orderBy, self::ORDER_BY)) {
            $orderBy = self::ORDER_BY[0];
        }
        $orderDescending = $request->input('descending') === 'true';
        $page = (int)$request->input('page') ?? 1;
        $perPage = $request->input('rowsPerPage');
        if (!in_array($perPage, self::PER_PAGE)) {
            $perPage = self::PER_PAGE[0];
        }
        $type = $request->input('type');

        $user = Auth::user();
        $userTagRights = $user->tagRightsRelation->pluck('id');

        $reportingsQuery = $this->reportingEngine->reportingsFilterQuery(appId(), $type, $orderBy, $orderDescending);
        $reportings = $reportingsQuery
            ->get();

        if (!$user->isFullAdmin()) {
            $reportings = $reportings->filter(function ($reporting) use ($userTagRights, $user) {
                $commonTags = $userTagRights->intersect($reporting->tag_ids);
                if ($commonTags->isEmpty()) {
                    return false;
                }
                return true;
            });
        }

        $reportingCount = $reportings->count();
        $reportings = $reportings->forPage($page, $perPage);

        $tagIds = $reportings->pluck('tag_ids')->flatten()->unique();
        $tags = Tag::where('app_id', appId())->whereIn('id', $tagIds)->get();

        $categoryIds = $reportings->pluck('category_ids')->flatten()->unique();
        $categories = Category::where('app_id', appId())->whereIn('id', $categoryIds)->get();

        $reportings = array_map(function ($reporting) use ($categories, $tags) {
            $reporting['tags'] = $tags->whereIn('id', $reporting['tag_ids'])->values()->toArray();
            unset($reporting['tag_ids']);

            $reporting['categories'] = $categories->whereIn('id', $reporting['category_ids'])->values()->toArray();
            unset($reporting['category_ids']);

            return $reporting;
        }, $reportings->values()->toArray());

        return response()->json([
            'reportings' => $reportings,
            'count' => $reportingCount,
        ]);
    }

    public function store(Request $request, PermissionEngine $permissionEngine)
    {
        $reporting = DB::transaction(function () use ($permissionEngine, $request) {
            $emails = parseEmails($request->input('emails'));
            $type = $request->input('type');
            $tagIds = $request->input('tag_ids', []);

            $tags = Tag::ofApp(appId())->whereIn('id', $tagIds)->get();

            if (!$emails) {
                abort(400, 'Die eingegebene Mail-Adresse ist ungültig.');
            }

            if (!in_array($type, [Reporting::TYPE_QUIZ, Reporting::TYPE_USERS])) {
                abort(400);
            }

            if ($tags->isEmpty() && !Auth::user()->isFullAdmin()) {
                app()->abort(400, 'Sie müssen mindestens einen TAG wählen.');
            }

            $reporting = new Reporting();
            $reporting->app_id = appId();
            $reporting->type = $type;
            $reporting->emails = $emails;
            $reporting->interval = ReportingEngine::INTERVAL_1M;
            $reporting->tag_ids = $this->getTagsToSave($tagIds, $reporting);
            $reporting->category_ids = [];
            $reporting->save();

            return $reporting;
        });

        return response()->json([
            'reporting' => $reporting,
        ]);
    }

    public function show($reportingId)
    {
        $reporting = $this->reportingEngine->getReporting($reportingId);
        return Response::json($this->getReportingResponse($reporting));
    }

    public function update($reportingId, Request $request, PermissionEngine $permissionEngine)
    {
        $reporting = $this->reportingEngine->getReporting($reportingId);
        $user = Auth::user();

        $emails = $request->input('emails');
        $emails = parseEmails($emails);
        $tagIds = $request->input('tag_ids', []);
        $categoryIds = $request->input('category_ids', []);
        $interval = $request->input('interval');

        $tags = Tag::ofApp(appId())->whereIn('id', $tagIds)->get();
        $categories = Category::ofApp(appId())->whereIn('id', $categoryIds)->get();

        if (!$emails) {
            abort(400, 'Die eingegebene Mail-Adresse ist ungültig.');
        }

        if ($tags->isEmpty() && !$user->isFullAdmin()) {
            app()->abort(400, 'Sie müssen mindestens einen TAG wählen.');
        }
        if (!in_array($interval, array_keys(ReportingEngine::INTERVAL_LABELS))) {
            app()->abort(400, 'Ungültiges Interval');
        }

        $reporting->tag_ids = $this->getTagsToSave($tagIds, $reporting);
        if ($reporting->type === Reporting::TYPE_QUIZ) {
            $reporting->category_ids = $categories->pluck('id');
        }
        $reporting->interval = $interval;
        $reporting->emails = $emails ?: [];
        $reporting->save();

        return Response::json($this->getReportingResponse($reporting));
    }

    public function deleteInformation($categoryId)
    {
        $reporting = $this->reportingEngine->getReporting($categoryId);
        return Response::json([
            'dependencies' => $reporting->safeRemoveDependees(),
            'blockers' => $reporting->getBlockingDependees(),
        ]);
    }

    public function delete($reportingId)
    {
        $reporting = $this->reportingEngine->getReporting($reportingId);

        $result = $reporting->safeRemove();

        if ($result->isSuccessful()) {
            return Response::json([], 204);
        } else {
            return Response::json($result->getMessages(), 400);
        }
    }

    private function getReportingResponse(Reporting $reporting)
    {
        $reporting = $reporting->toArray();

        $reporting['tag_ids'] = array_map(function ($tag) {
            return (int)$tag;
        }, $reporting['tag_ids']);

        $reporting['category_ids'] = array_map(function ($category) {
            return (int)$category;
        }, $reporting['category_ids']);

        $reporting['emails'] = implode(', ', $reporting['emails']);

        return [
            'reporting' => $reporting,
        ];
    }

    private function getTagsToSave($newTags, Reporting $reporting) {
        $oldTags = $reporting->tag_ids;
        if(is_null($oldTags)) {
            $oldTags = [];
        }

        $usersTags = Auth::user()->tagRightsRelation->pluck('id');

        $blockedTags = [];
        if ($usersTags->count() > 0) {
            foreach ($oldTags as $tag) {
                if ($usersTags->search($tag) === false) {
                    $blockedTags[] = $tag;
                }
            }
        }

        if(!empty($blockedTags)) {
            $newTags = array_unique(array_merge($newTags, $blockedTags));
        }

        return Tag::where('app_id', appId())
            ->whereIn('id', $newTags)
            ->pluck('id')
            ->toArray();
    }
}
