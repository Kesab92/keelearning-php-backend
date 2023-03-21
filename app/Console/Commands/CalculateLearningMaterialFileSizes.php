<?php

namespace App\Console\Commands;

use App\Models\LearningMaterialTranslation;
use Illuminate\Console\Command;

class CalculateLearningMaterialFileSizes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'learningmaterials:calculatefilesize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculates the file sizes of learning material attachments';

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
     * @return mixed
     */
    public function handle()
    {
        $learningMaterialTranslations = LearningMaterialTranslation::where('file', '!=', '')->get();
        if (! $learningMaterialTranslations->count()) {
            return;
        }
        $this->line('Recalculating file size of '.$learningMaterialTranslations->count().' learning material attachments.');
        $bar = $this->output->createProgressBar($learningMaterialTranslations->count());
        $warnings = [];
        foreach ($learningMaterialTranslations as $learningMaterialTranslation) {
            $filePath = public_path($learningMaterialTranslation->file);
            if (file_exists($filePath)) {
                $learningMaterialTranslation->file_size_kb = ceil(filesize($filePath) / 1024);
                $learningMaterialTranslation->save();
            } else {
                $warnings[] = 'Could not find file `'.$learningMaterialTranslation->file.'` of Learning Material #'.$learningMaterialTranslation->learning_material_id;
            }
            $bar->advance();
        }
        $bar->finish();
        $this->line('');
        foreach ($warnings as $warning) {
            $this->warn($warning);
        }
    }
}
