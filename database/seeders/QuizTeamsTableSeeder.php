<?php

namespace Database\Seeders;

use App\Models\QuizTeam;
use App\Models\QuizTeamMember;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuizTeamsTableSeeder extends Seeder
{
    /**
     * Generates 100 quiz teams with 100 users each.
     *
     * @return void
     */
    public function run()
    {
        QuizTeam::factory()->count(100)
                ->create()
                ->each(function ($quizTeam) {
                    $allAppUsers = DB::table('users')
                                     ->where('app_id', $quizTeam->app_id)
                                     ->get();

                    for ($i = 0; $i < 100; $i++) {
                        //get random app user array key
                        $randomArrayKey = array_rand($allAppUsers, 1);

                        QuizTeamMember::factory()->count(1)->create([
                                'quiz_team_id' => $quizTeam->id,
                                'user_id'  => $allAppUsers[$randomArrayKey]->id,
                        ]);

                        //remove user from pool so it can't be added again
                        unset($allAppUsers[$randomArrayKey]);
                    }
                });
    }
}
