<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Competition;
use App\Models\IndexCard;
use App\Models\LearningMaterial;
use App\Models\LearningMaterialFolder;
use App\Models\LearningMaterialTranslation;
use App\Models\News;
use App\Models\QuestionAttachment;
use App\Models\User;
use DB;
use Illuminate\Console\Command;
use Illuminate\Http\File;
use Intervention\Image\Facades\Image;
use Storage;
use Str;

class MigrateAssetsToAzure extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:azure:assets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate file assets to new storage api';

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
     */
    public function handle()
    {
        $data = [
            'categories' => [
                'cover_image',
                'category_icon',
            ],
            'competitions' => [
                'cover_image',
            ],
            'index_cards' => [
                'cover_image',
            ],
            'learning_materials' => [
                'cover_image',
            ],
            'learning_material_folders' => [
                'folder_icon',
            ],
            'learning_material_translations' => [
                'file',
            ],
            'news' => [
                'cover_image',
            ],
            'question_attachments' => [
                'attachment',
            ],
            'users' => [
                'avatar',
            ],
            'certificate_templates' => [
                'background_image',
            ],
        ];

        foreach ($data as $table => $columns) {
            $this->info('Starting to migrate table '.$table);
            $models = \DB::table($table)->get();
            $this->info('Found '.$models->count().' models');
            foreach ($models as $model) {
                if ($table === 'question_attachments') {
                    if ($model->type === QuestionAttachment::ATTACHMENT_TYPE_YOUTUBE) {
                        continue;
                    }
                }
                try {
                    foreach ($columns as $column) {
                        if (strlen($model->$column) > 0) {
                            DB::update('update '.$table.' set '.$column.' = ? where id = ?', [$this->generateColumnURL($model->$column), $model->id]);
                        }

                        $newColumn = $column.'_url';
                        if (strlen($model->$newColumn) > 0) {
                            DB::update('update '.$table.' set '.$newColumn.' = ? where id = ?', [$this->generateNewColumnURL($model->$newColumn), $model->id]);
                        }
                    }
                } catch (\Exception $e) {
                    \Log::info($e->__toString());
                    exit;
                }
            }
        }
        $this->info('Done');
    }

    private function generateColumnURL($old)
    {
        $basename = pathinfo($old, PATHINFO_BASENAME);

        return 'uploads/'.$basename;
    }

    private function generateNewColumnURL($old)
    {
        $basename = pathinfo($old, PATHINFO_BASENAME);

        return 'https://keelearningdiag.blob.core.windows.net/laravel-file-storage-prod/uploads/'.$basename;
    }
}
