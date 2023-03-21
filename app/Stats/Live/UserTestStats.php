<?php
namespace App\Stats\Live;

use App\Models\TestSubmission;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class UserTestStats {
    public function attach(Collection $data) {
        if(!$data->count()) {
            return;
        }
        $testsData = TestSubmission
            ::whereIn('user_id', $data->pluck('id'))
            ->select('user_id', 'test_id', \DB::raw('MAX(result) as result'))
            ->groupBy(['user_id', 'test_id'])
            ->get()
            ->groupBy('user_id');

        $data->transform(function($user) use ($testsData) {
            $user['passed_tests'] = collect([]);
            $user['failed_tests'] = collect([]);
            $user['attempted_tests'] = collect([]);
            collect($testsData->get($user['id'], []))->each(function($data) use ($user) {
                if($data['result'] === 1) {
                    $user['passed_tests'][] = $data['test_id'];
                } else if ($data['result'] === 0) {
                    $user['failed_tests'][] = $data['test_id'];
                } else if ($data['result'] === null) {
                    $user['attempted_tests'][] = $data['test_id'];
                }
            });

            return $user;
        });
    }
}
