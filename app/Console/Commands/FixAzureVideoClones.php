<?php

namespace App\Console\Commands;

use App\Models\AzureVideo;
use App\Models\LearningMaterialTranslation;
use Carbon\Carbon;
use Illuminate\Console\Command;

class FixAzureVideoClones extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'azure:fixclones';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fixes cloned azure video learning materials that miss their entry in azure_videos';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        /*
         * SELECT * FROM learning_material_translations
         * LEFT JOIN azure_videos ON learning_material_translations.file = azure_videos.id
         * LEFT JOIN learning_materials ON learning_material_translations.learning_material_id = learning_materials.id
         * LEFT JOIN learning_material_folders ON learning_materials.learning_material_folder_id = learning_material_folders.id
         * WHERE azure_videos.app_id != learning_material_folders.app_id
         * AND learning_material_translations.file_type = 'azure_video'
         *
         */
        $learningMaterialTranslationIds = LearningMaterialTranslation::select('learning_material_translations.id')
            ->leftJoin('azure_videos', 'learning_material_translations.file', '=', 'azure_videos.id')
            ->leftJoin('learning_materials', 'learning_material_translations.learning_material_id', '=', 'learning_materials.id')
            ->leftJoin('learning_material_folders', 'learning_materials.learning_material_folder_id', '=', 'learning_material_folders.id')
            ->where('learning_material_translations.file_type', 'azure_video')
            ->whereRaw('azure_videos.app_id != learning_material_folders.app_id');
        $learningMaterialTranslations = LearningMaterialTranslation::whereIn('id', $learningMaterialTranslationIds)
            ->with('azureVideo')
            ->with('learningMaterial.learningMaterialFolder')
            ->get();

        foreach ($learningMaterialTranslations as $learningMaterialTranslation) {
            $azureVideoClone = $learningMaterialTranslation->azureVideo->replicate();
            $azureVideoClone->app_id = $learningMaterialTranslation->learningMaterial->learningMaterialFolder->app_id;
            $azureVideoClone->created_at = Carbon::now();
            $azureVideoClone->save();
            $learningMaterialTranslation->file = $azureVideoClone->id;
            $learningMaterialTranslation->save();
        }

        return 0;
    }
}
