<?php
namespace App\Stats\Live;

use App\Models\Courses\CourseParticipation;
use Illuminate\Support\Collection;

class UserCourseStats {
    public function attach(Collection $data) {
        if(!$data->count()) {
            return;
        }
        $coursesData = CourseParticipation
            ::whereIn('user_id', $data->pluck('id'))
            ->select('user_id', 'course_id', \DB::raw('MAX(passed) as passed'))
            ->groupBy(['user_id', 'course_id'])
            ->get()
            ->groupBy('user_id');

        $data->transform(function($user) use ($coursesData) {

            $user['passed_courses'] = collect([]);
            $user['failed_courses'] = collect([]);
            $user['attempted_courses'] = collect([]);
            collect($coursesData->get($user['id'], []))->each(function($data) use ($user) {
                if($data['passed'] === 1) {
                    $user['passed_courses'][] = $data['course_id'];
                } else if ($data['passed'] === 0) {
                    $user['failed_courses'][] = $data['course_id'];
                } else if ($data['passed'] === null) {
                    $user['attempted_courses'][] = $data['course_id'];
                }
            });

            return $user;
        });
    }
}
