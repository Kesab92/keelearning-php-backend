<?php

namespace App\Console\Commands;

use App\Traits\Translatable;
use DB;
use Illuminate\Console\Command;

class ManageDuplicateTranslations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translations:duplicates {model : Model whose translations should be checked}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Helps prune duplicate translations';

    protected $parentIdColumn;
    protected $translatedFields;
    protected $translationClass;
    protected $youngestCreatedAt;

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
        $modelClass = '\\App\\Models\\' . $this->argument('model');
        if (!class_exists($modelClass)) {
            $this->error('Class ' . $modelClass . ' does not exist!');
            return;
        }
        if (!in_array(Translatable::class, class_uses($modelClass))) {
            $this->error('Class ' . $modelClass . ' does not have translations!');
            return;
        }
        $modelInstance = new $modelClass;
        $this->translationClass = $modelInstance->getTranslationModelName();
        $this->translationTable = $modelInstance->getTranslationTableName();
        $this->translatedFields = $modelInstance->translated;
        $this->parentIdColumn = $modelInstance->getForeignIdColumn();

        $duplicates = $this->getDuplicates();
        if(!$duplicates->count()) {
            $this->info('No duplicates found!');
            return;
        }
        $this->info('Found ' . $duplicates->count() . ' translations with duplicate entries.');

        $this->line('Checking for auto-deletable full duplicates...');
        $this->output->progressStart($duplicates->count());
        $affectedModelIds = [];
        $idsToDelete = [];
        foreach($duplicates as $duplicate) {
            $translations = $this->translationClass::where($this->parentIdColumn, $duplicate->parent_id)
                ->where('language', $duplicate->language)
                ->get();
            // compare each translation in the collection to those after it
            for ($ii = 0; $ii < $translations->count(); $ii++) {
                $translation = $translations->get($ii);
                $this->checkCreatedAt($translation);
                for ($jj = $ii + 1; $jj < $translations->count(); $jj++) {
                    $translationComparison = $translations->get($jj);
                    $this->checkCreatedAt($translationComparison);
                    if ($this->isFullDuplicate($translation, $translationComparison)) {
                        $idsToDelete[] = $translationComparison->id;
                        $affectedModelIds[] = $translation[$this->parentIdColumn];
                    }
                }
            }
            $this->output->progressAdvance();
        }
        $this->output->progressFinish();
        $idsToDelete = collect($idsToDelete)->unique()->values();
        $this->translationClass::whereIn('id', $idsToDelete)->delete();
        $this->info('Deleted ' . $idsToDelete->count() . ' duplicates automatically.');
        $this->line('Affected Model IDs: ' . collect($affectedModelIds)->unique()->join(','));

        $duplicates = $this->getDuplicates();
        if(!$duplicates->count()) {
            $this->info('No more duplicates left!');
            return;
        }
        $this->info($duplicates->count() . ' duplicates which need manual intervention left.');
        $idsToDelete = collect([]);
        $affectedModelIds = [];
        foreach($duplicates as $duplicate) {
            $translations = $this->translationClass::where($this->parentIdColumn, $duplicate->parent_id)
                ->where('language', $duplicate->language)
                ->get();
            $selectionId = $this->selectTranslation($translations);
            if ($selectionId === null) {
                break;
            }
            $idsToDelete = $idsToDelete->concat($translations->where('id', '!=', $selectionId)->pluck('id'));
            $affectedModelIds[] = $duplicate->parent_id;
        }
        $this->translationClass::whereIn('id', $idsToDelete)->delete();
        $this->info('Manually deleted ' . $idsToDelete->count() . ' duplicate translations.');
        $this->info('Youngest duplicate was created at ' . $this->youngestCreatedAt);
        $this->line('Affected Model IDs: ' . collect($affectedModelIds)->unique()->join(','));
    }

    private function getDuplicates()
    {
        // SELECT {parent_id} as parent_id, language, COUNT(*) as count FROM {translations_table} GROUP BY {parent_id}, language HAVING COUNT(*) > 1
        return DB::table($this->translationTable)
            ->selectRaw($this->parentIdColumn . ' as parent_id, language, COUNT(*) as count')
            ->groupByRaw('parent_id, language')
            ->havingRaw('COUNT(*) > 1')
            ->get();
    }

    private function isFullDuplicate($translation, $translationComparison)
    {
        foreach ($this->translatedFields as $translatedField) {
            if (utrim($translation->{$translatedField}) != utrim($translationComparison->{$translatedField})) {
                return false;
            }
        }
        return true;
    }

    private function selectTranslation($translations)
    {
        $this->info($this->argument('model') . ' #' . $translations[0]->{$this->parentIdColumn});
        $this->line('Select which translation to keep:');
        $ii = 1;
        foreach ($translations as $translation) {
            $this->checkCreatedAt($translation);
            $this->info('[' . $ii . ']');
            foreach ($this->translatedFields as $translatedField) {
                $this->line('   ' . $translatedField . ': ' . $translation->{$translatedField});
            }
            $ii += 1;
        }
        $id = (int) $this->ask('Which translation # should be kept? Enter 0 to finish');
        if ($id == 0) {
            return null;
        }
        if ($id <= 0 || $id > $translations->count()) {
            $this->error('Invalid selection!');
            return $this->selectTranslation($translations);
        }
        return $translations->get($id - 1)->id;
    }

    private function checkCreatedAt($translation)
    {
        if (!$this->youngestCreatedAt || $this->youngestCreatedAt < $translation->created_at) {
            $this->youngestCreatedAt = $translation->created_at;
        }
    }
}
