<?php

namespace App\Http\Controllers\Backend;

use App\Exports\DefaultExport;
use App\Http\Controllers\Controller;
use App\Models\AnalyticsEvent;
use App\Models\App;
use App\Models\LearningMaterial;
use App\Models\News;
use App\Services\ViewEngine;
use DB;
use Maatwebsite\Excel\Facades\Excel;
use View;

class StatsViewsController extends Controller
{
    protected $fileExtension = 'xlsx';

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:stats_views,settings-viewcounts');
        View::share('activeNav', 'stats.views');
    }

    /**
     * Shows the view stats.
     *
     * @param ViewEngine $viewEngine
     * @return mixed
     * @throws \Exception
     */
    public function index(ViewEngine $viewEngine)
    {
        $viewData = [
            'entries' => $this->getViewStatEntries(appId()),
        ];

        return view('stats.views.main', $viewData);
    }

    /**
     * Downloads view stats as csv.
     *
     * @return mixed
     * @throws \Exception
     */
    public function csv()
    {
        $entries = $this->getViewStatEntries(appId());

        $data = [
            'entries' => $entries,
        ];

        return Excel::download(new DefaultExport($data, 'stats.views.csv'), 'statistiken-aufrufe.xlsx');
    }

    private function getViewStatEntries($appId)
    {
        $entries = [];

        $viewcountApp = AnalyticsEvent
            ::select([DB::raw('COUNT(*) as views, MIN(created_at) as created_at')])
            ->where('type', AnalyticsEvent::TYPE_VIEW_HOME)
            ->where('app_id', $appId)
            ->first();
        if ($viewcountApp) {
            $entries[] = [
                'title'      => 'Startseite',
                'views'      => $viewcountApp->views,
                'created_at' => $viewcountApp->created_at,
            ];
        } else {
            $entries[] = [
                'title'      => 'Startseite',
                'views'      => 'n/a',
                'created_at' => null,
            ];
        }

        $learningMaterialEntry = [
            'title'      => 'Alle Dateien aus der Mediathek',
            'views'      => 0,
            'created_at' => null,
        ];
        $learningMaterials = LearningMaterial
            ::with('translationRelation', 'learningMaterialFolder')
            ->whereHas('learningMaterialFolder', function ($q) use ($appId) {
                $q->where('app_id', $appId);
            })->get()->keyBy('id');
        $viewcountsLearningMaterials = AnalyticsEvent
            ::select([DB::raw('COUNT(*) as views, MIN(created_at) as created_at, foreign_id')])
            ->where('type', AnalyticsEvent::TYPE_VIEW_LEARNING_MATERIAL)
            ->where('foreign_type', (new LearningMaterial())->getMorphClass())
            ->whereIn('foreign_id', $learningMaterials->keys())
            ->groupBy('foreign_id')
            ->get();

        foreach ($viewcountsLearningMaterials as $viewcount) {
            $learningMaterialEntry['views'] += $viewcount->views;
            if ($learningMaterialEntry['created_at'] === null || $learningMaterialEntry['created_at']->gt($viewcount->created_at)) {
                $learningMaterialEntry['created_at'] = $viewcount->created_at;
            }
        }
        $entries[] = $learningMaterialEntry;
        foreach ($viewcountsLearningMaterials as $viewcount) {
            $entries[] = [
                'title'      => 'Lernmaterial: '.$learningMaterials->get($viewcount->foreign_id)->title,
                'views'      => $viewcount->views,
                'created_at' => $viewcount->created_at,
            ];
        }

        $newsEntry = [
            'title'      => 'Alle News',
            'views'      => 0,
            'created_at' => null,
        ];
        $news = News
            ::with('translationRelation')
            ->where('app_id', $appId)->get()->keyBy('id');
        $viewcountsNews = AnalyticsEvent
            ::select([DB::raw('COUNT(*) as views, MIN(created_at) as created_at, foreign_id')])
            ->where('type', AnalyticsEvent::TYPE_VIEW_NEWS)
            ->where('foreign_type', (new News())->getMorphClass())
            ->whereIn('foreign_id', $news->keys())
            ->groupBy('foreign_id')
            ->get();
        foreach ($viewcountsNews as $viewcount) {
            $newsEntry['views'] += $viewcount->views;
            if ($newsEntry['created_at'] === null || $newsEntry['created_at']->gt($viewcount->created_at)) {
                $newsEntry['created_at'] = $viewcount->created_at;
            }
        }
        $entries[] = $newsEntry;
        foreach ($viewcountsNews as $viewcount) {
            $entries[] = [
                'title'      => 'News: '.$news->get($viewcount->foreign_id)->title,
                'views'      => $viewcount->views,
                'created_at' => $viewcount->created_at,
            ];
        }

        return $entries;
    }
}
