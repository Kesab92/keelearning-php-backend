<?php

namespace App\Http\Controllers\Api\Custom;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\Test;
use Response;

class CuratorController extends Controller
{
    public function tests()
    {
        $tests = Test::ofApp(App::ID_CURATOR)
            ->where(function ($q) {
                $q->where('active_until', '>', date('Y-m-d H:i:s'))
                    ->orWhereNull('active_until');
            })
            ->where('attempts', '!=', 1)
            ->where(function ($q) {
                $q->where('quiz_team_id', '<=', 0)
                    ->orWhereNull('quiz_team_id');
            })
            ->whereDoesntHave('tags')
            ->get();
        $appHostedAt = App::findOrFail(App::ID_CURATOR)
            ->getDefaultAppProfile()
            ->app_hosted_at;
        $tests = $tests->map(function ($test) use ($appHostedAt) {
            /** @var Test $test */
            $questionCount = $test->testQuestions()->count();

            return [
                'id' => $test->id,
                'name' => $test->name,
                'url' => $appHostedAt.'/tests/'.$test->id,
                'question_count' => $questionCount,
                'duration' => $test->minutes ?: ceil($questionCount / 2),
            ];
        });

        return Response::json($tests);
    }
}
