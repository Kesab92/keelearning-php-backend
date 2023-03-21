<?php

namespace App\Services;

use App\Models\App;
use App\Models\AnalyticsEvent;
use Cache;

class ViewEngine
{
    /**
     * Calculates the first view date of an app.
     * @param $appId
     * @return mixed
     */
    public function calculateFirstViewDate($appId)
    {
        return Cache::rememberForever('apps-first-view-date-'.$appId, function () use ($appId) {
            $firstViewCount = AnalyticsEvent::where('type', AnalyticsEvent::TYPE_VIEW_HOME)
                ->orderBy('created_at', 'asc')
                ->ofApp($appId)
                ->limit(1)
                ->first();
            if ($firstViewCount) {
                return $firstViewCount->created_at;
            }
        });
    }
}
