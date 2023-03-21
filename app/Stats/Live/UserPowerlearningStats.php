<?php
namespace App\Stats\Live;

use App\Models\LearnBoxCard;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class UserPowerlearningStats {
    public function attach(Collection $data) {
        if(!$data->count()) {
            return;
        }
        $boxData = LearnBoxCard
            ::whereIn('user_id', $data->pluck('id'))
            ->where('box', '>', 0)
            ->where('type', LearnBoxCard::TYPE_QUESTION)
            ->select('user_id', \DB::raw('COUNT(*) as questions'))
            ->groupBy('user_id')
            ->get()
            ->pluck('questions', 'user_id');

        $data->transform(function($user) use ($boxData) {
            $user['learned_questions'] = $boxData->get($user['id'], 0);

            return $user;
        });
    }
}
