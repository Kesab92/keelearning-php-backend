<?php

namespace App\Console\Commands;

use App\Models\CertificateTemplate;
use App\Models\Courses\CourseChapter;
use App\Models\Courses\CourseContent;
use Illuminate\Console\Command;

class TranslationsMigrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translations:migrate {model : Model to migrate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrates phrases to the new translation system';

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
        $modelName = '\\App\\Models\\'.$this->argument('model');
        $models = $modelName::all();
        $this->line('Migrating '.$modelName.'...');
        $bar = $this->output->createProgressBar(iterator_count($models));
        foreach ($models as $model) {
            $translationModelName = $model->getTranslationModelName();
            $translation = new $translationModelName();
            $translation->{$model->getForeignIdColumn()} = $model->id;
            foreach ($model->translated as $key) {
                $translation->{$key} = $model->getRawOriginal($key);
            }
            $app_id = $this->getAppId($model);
            if ($app_id === null) {
                $logMsg = 'Couldnt get the app id for ' . $this->argument('model') . ' #' . $model->id . '. Setting language to "de"';
                logger($logMsg);
                $this->info($logMsg);
                $translation->language = 'de';
            } else {
                $translation->language = defaultAppLanguage($app_id);
            }
            $translation->save();
            $bar->advance();
        }
        $bar->finish();
        $this->line('');
        $this->info('All done!');
    }

    private function getAppId($model)
    {
        switch(get_class($model)) {
            case CertificateTemplate::class:
                if($model->test_id) {
                    return $model->test->app_id;
                } else {
                    $courseContent = CourseContent
                        ::where('type', CourseContent::TYPE_CERTIFICATE)
                        ->where('foreign_id', $model->id)
                        ->first();
                    if($courseContent) {
                        return $courseContent->chapter->course->app_id;
                    } else {
                        return null;
                    }
                }
            default:
                return $model->app_id;
        }
    }
}
