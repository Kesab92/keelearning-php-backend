<?php

namespace Database\Seeders;

use App\Models\App;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class AppTableSeeder extends Seeder
{
    /**
     * Generates 4 apps with 4 categories each.
     *
     * @return void
     */
    public function run()
    {

        //generating sopamo app with categories
        $sopamoApp = App::factory()->count(1)->create(['name' => 'Sopamo Demo']);
        Category::factory()->count(4)->create(['app_id' => $sopamoApp->id]);

        //generating default app with categories
        App::factory()->count(3)
                ->create()
                ->each(function ($app) {
                    Category::factory()->count(4)->create(['app_id' => $app->id]);
                });
    }
}
