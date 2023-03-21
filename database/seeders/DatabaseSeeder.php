<?php

namespace Database\Seeders;

use Database\Seeders\AppTableSeeder;
use Database\Seeders\GamesTableSeeder;
use Database\Seeders\QuizTeamsTableSeeder;
use Database\Seeders\PagesTableSeeder;
use Database\Seeders\QuestionsTableSeeder;
use Database\Seeders\SuggestedQuestionsTableSeeder;
use Database\Seeders\UserTableSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run all the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        //WARNING: changing the seeding order may break the whole seeding operation!

        $this->call(AppTableSeeder::class);
        $this->call(UserTableSeeder::class);
        $this->call(QuizTeamsTableSeeder::class);
        $this->call(PagesTableSeeder::class);
        $this->call(SuggestedQuestionsTableSeeder::class);
        $this->call(QuestionsTableSeeder::class);
        $this->call(GamesTableSeeder::class);

        // Only run this single seeder to create a small dataset in the game relevant tables
//        $this->call(SmallTablesSeeder::class);

        Model::reguard();
    }
}
