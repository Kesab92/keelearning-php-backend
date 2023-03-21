<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserTableSeeder extends Seeder
{
    /**
     * Generates 5000 users.
     *
     * @return void
     */
    public function run()
    {

        /*
         * Add our developer accounts.
         * To generate a password for yourself, run the following:
         *      echo Hash::make("password");
         * If you add your account, please decrease the dummy user factory by 1.
         */

        DB::table('users')
          ->insert([
                  'app_id'       => 1,
                  'username'     => 'Paul',
                  'email'        => 'p.mohr@sopamo.de',
                  'password'     => '$2y$10$Vpe/V4zl6BHtiJXtcdiUoODD4Uq0gwXH2TF7jTdJu8uAZ11bg4LkS',
                  'tos_accepted' => true,
          ]);

        DB::table('users')
          ->insert([
                  'app_id'       => 1,
                  'username'     => 'Fabiano',
                  'email'        => 'f.henkel@sopamo.de',
                  'password'     => '$2y$10$DAixWvp56u5QsRU6Orl2ler5DwJFKjW37cszLu4Gq5krubvoK2Bjy', //yolo
                  'tos_accepted' => true,
          ]);

        DB::table('users')
          ->insert([
                  'app_id'       => 1,
                  'username'     => 'moe',
                  'email'        => 'm.kraus@sopamo.de',
                  'password'     => '$2y$10$vel82sEmtC/fZPWgNNOKauZQguRR7P70xAR8F.KjLjucPUd9Phvwe',
                  'tos_accepted' => true,
          ]);

        // Add some more dummy users
        User::factory()->count(4997)->create();
    }
}
