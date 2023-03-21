<?php

namespace App\Console\Commands\OneOff;

use App\Models\App;
use App\Models\Viewcount;
use App\Services\MorphTypes;
use Illuminate\Console\Command;

/**
 * Class CacheStats.
 */
class MigrateLearningmaterialViews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'learningmaterials:migrateviews';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate views from classes to morph id';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Viewcount::where('foreign_type', 'App\Models\LearningMaterial')
            ->update([
                'foreign_type' => MorphTypes::TYPE_LEARNINGMATERIAL,
            ]);
    }
}
