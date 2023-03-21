<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PagesTableSeeder extends Seeder
{
    /**
     * Generates 100 pages.
     *
     * @return void
     */
    public function run()
    {
        Page::factory()->count(100)->create();
    }
}
