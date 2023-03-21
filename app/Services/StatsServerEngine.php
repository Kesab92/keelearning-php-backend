<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class StatsServerEngine
{
    /**
     * Request stats data from internal StatsServer
     *
     * @param array $stats associative array, statskey => settings
     * @param int $appId fall back to current app id
     *
     * @return array associative array, statskey => stats
     */
    public function getStats(array $stats, ?int $appId = null): array
    {
        return Http::post(env('STATS_SERVER_API_URL') . 'stats', [
            'app_id' => $appId ?: appId(),
            'stats' => $stats,
        ])->json();
    }
}
