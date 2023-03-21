<?php

namespace App\Console\Commands;

use App\Models\App;
use App\Models\LearningMaterial;
use App\Models\News;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PruneViews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'views:prune {appid}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes all viewcounts of an app';

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
        $appId = $this->argument('appid');
        $app = App::find($appId);

        if (! $app) {
            $this->error('Could not find app with id #'.$appId);

            return;
        }

        if (! $this->confirm('Is "'.$app->name.'" the app from which you want to delete all viewcount data?')) {
            return;
        }

        $app->viewcounts()->delete();

        $news = News::where('app_id', $app->id)->get();
        $this->info('Deleting '.$news->count().' news entry viewcounts…');
        $bar = $this->output->createProgressBar($news->count());
        foreach ($news as $newsEntry) {
            $newsEntry->viewcounts()->delete();
            $bar->advance();
        }
        $bar->finish();
        $this->line('');

        $learningMaterials = LearningMaterial::whereHas('learningMaterialFolder', function ($query) use ($app) {
            $query->where('app_id', $app->id);
        })->get();
        $this->info('Deleting '.$learningMaterials->count().' learning material viewcounts…');
        $bar = $this->output->createProgressBar($learningMaterials->count());
        foreach ($learningMaterials as $learningMaterial) {
            $learningMaterial->viewcounts()->delete();
            $bar->advance();
        }
        $bar->finish();
        $this->line('');

        $this->info('All done!');
    }
}
