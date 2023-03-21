<?php

namespace App\Traits;

use App\Models\AnalyticsEvent;
use App\Models\User;
use DB;
use Illuminate\Support\Collection;

trait Views
{
    public function addView(User $user)
    {
        AnalyticsEvent::log($user, AnalyticsEvent::VIEW_EVENT_MAPPING[get_class($this)], $this);
    }

    public static function attachViewcountTotals(Collection $models)
    {
        if (! $models->count()) {
            return $models;
        }
        $eventType = AnalyticsEvent::VIEW_EVENT_MAPPING[get_class($models->first())];
        $totals = AnalyticsEvent
            ::select([DB::raw('COUNT(*) as views'), 'foreign_id'])
            ->where('foreign_type', $models->first()->getMorphClass())
            ->whereIn('foreign_id', $models->pluck('id'))
            ->where('type', $eventType)
            ->groupBy('foreign_id')
            ->pluck('views', 'foreign_id');
        $models->transform(function ($model) use ($totals) {
            $model->viewcount_total = $totals->get($model->id, 0);
            return $model;
        });

        return $models;
    }

    /**
     * @param Collection $models
     * @param User $user
     * @return Collection
     */
    public static function attachLastViewedAt(Collection $models, User $user)
    {
        if (! $models->count()) {
            return $models;
        }
        $eventType = AnalyticsEvent::VIEW_EVENT_MAPPING[get_class($models->first())];
        $hasViewed = AnalyticsEvent
            ::select([DB::raw('MAX(created_at) as last_viewed'), 'foreign_id'])
            ->where('foreign_type', $models->first()->getMorphClass())
            ->whereIn('foreign_id', $models->pluck('id'))
            ->where('type', $eventType)
            ->where('user_id', $user->id)
            ->groupBy('foreign_id')
            ->pluck('last_viewed', 'foreign_id');
        $models->transform(function ($model) use ($hasViewed) {
            $model->last_viewed_at = $hasViewed->get($model->id, null);
            return $model;
        });

        return $models;
    }
}
