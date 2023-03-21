<?php
namespace App\Stats\Live;

use App\Models\AnalyticsEvent;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LastOnline {
    public function attach(Collection $data) {
        $dates = AnalyticsEvent
            ::whereIn('user_id', $data->pluck('id'))
            ->select(['user_id', \DB::raw('MAX(created_at) as date')])
            ->groupBy('user_id')
            ->pluck('date', 'user_id');
        $recently = Carbon::now()->subDays(8);
        $data->transform(function($user) use ($dates, $recently) {
            $user['last_online'] = $dates->get($user['id'], null);
            if($user['last_online']) {
                $date = Carbon::createFromTimeStamp(strtotime($user['last_online']));
                if($date->gte($recently)) {
                    $user['last_online'] = 'kürzlich';
                } else {
                    $user['last_online'] = $date->diffForHumans();
                }
            }
            return $user;
        });
    }
}
