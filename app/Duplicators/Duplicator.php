<?php

namespace App\Duplicators;

use App\Models\App;
use App\Models\CloneRecord;
use App\Services\MorphTypes;
use App\Traits\Translatable;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;

/**
 * Class Duplicator.
 */
class Duplicator
{
    protected Model $original;
    protected Model $clone;

    /** properties that will be unset */
    protected function removeProperties()
    {
        return [
            'created_at',
            'updated_at',
        ];
    }

    /** associative array with properties to override */
    protected function setProperties(): array { return []; }

    /** relationships which will be related to the cloned entry. Duplicator won't create new entries in DB with these relationships */
    protected function keepRelationships(): array { return []; }

    /** relationships which will be related to the cloned entry. Duplicator will create new entries in DB with these relationships */
    protected function duplicateRelationships(): array { return []; }

    /** should the first created clone be re-used for further cloning attempts in this app? */
    protected function cloneOnlyOnce(): bool { return false; }

    /** Indicates if the given relationship belongs to the parent-chain */
    public function isParentDependency(string $relationship): bool { return false; }

    /** is this a child process, cloning a dependency of the originally cloned model? */
    private $_isChildProcess = false;

    /** is this a child process restoring the parent dependency chain? */
    private bool $_isCloningParentDependency = false;

    /** clone to different app? */
    private $_targetAppId = null;

    /** metadata not yet in the DB due to the transaction still pending */
    protected $_metadata;

    /** collection of warnings thrown during the current cloning process */
    protected static ?Collection $_warnings;

    /**
     * Checks if the cloning can and should be done.
     * It's important to throw an Exception on rejection,
     * so all pending parent and sibling cloning processes are rolled back.
     *
     * @return void
     * @throws Exception
     */
    protected function validateCloningProcess(): void
    {
        // if we want to store clone records,
        // the model needs to support fetching the parent app
        if ($this->cloneOnlyOnce() && !$this->original->app) {
            throw new Exception('Could not find parent app for ' . get_class($this->original));
        }
        if ($this->isCloningAcrossApps()) {
            if (!App::find($this->_targetAppId)) {
                throw new Exception('Target app does not exist!');
            }
        }
    }

    /**
     * Takes the model to be cloned as parameter.
     *
     * @param Model $original
     */
    public function __construct(Model $original)
    {
        $this->original = $original;
        $this->_metadata = [
            'clonerecords' => collect(),
            'clones' => collect(),
            'root' => null,
            'source_app_id' => null,
        ];
    }

    /**
     * Chainable method to set the target app ID to be cloned to, `null` keeps the current app.
     *
     * @param integer|null $appId
     * @return Duplicator
     */
    public function setTargetApp(?int $appId): Duplicator
    {
        $this->_targetAppId = $appId;
        return $this;
    }

    /**
     * Chainable method to pass along the metadata not yet in the DB.
     *
     * @param integer|null $appId
     * @return Duplicator
     */
    public function setMetadata(array $metadata): Duplicator
    {
        $this->_metadata = $metadata;
        return $this;
    }

    /**
     * Checks if the target app differs from the source app.
     *
     * @return boolean
     */
    protected function isCloningAcrossApps(): bool
    {
        if($this->_targetAppId === null || $this->_targetAppId === $this->_metadata['source_app_id']) {
            return false;
        }
        return true;
    }

    /**
     * Checks if the model uses translations.
     *
     * @return boolean
     */
    protected function isTranslatable(): bool
    {
        return in_array(Translatable::class, class_uses($this->original));
    }

    /**
     * Runs the duplicator, returning the newly created and saved model.
     *
     * @return Model
     */
    public function duplicate(): Model
    {
        $this->validateCloningProcess();
        if (!$this->isChildProcess()) {
            $this->_metadata['root'] = get_class($this);
            self::$_warnings = collect();
            if (!$this->original->app_id) {
                throw new Exception(get_class($this->original) . '#' . $this->original->id . ' has no app_id!');
            }
            $this->_metadata['source_app_id'] = $this->original->app_id;
        }
        if ($previousClone = $this->getPreviousClone()) {
            return $previousClone;
        }

        $this->clone = $this->original->replicate();
        $this->setCloneProperties();
        $this->clone->save();

        if ($this->cloneOnlyOnce()) {
            // For some reason the transaction does prevent
            // us from checking which models we already created,
            // so we store it manually and pass it along.
            $this->_metadata['clones']->push([
                'id' => $this->clone->id,
                'model' => $this->clone,
                'type' => MorphTypes::MAPPING[get_class($this->clone)],
            ]);
            $this->_metadata['clonerecords']->push(CloneRecord::create([
                'source_id'     => $this->original->id,
                'target_app_id' => $this->isCloningAcrossApps() ? $this->_targetAppId : $this->original->app->id,
                'target_id'     => $this->clone->id,
                'type'          => MorphTypes::MAPPING[get_class($this->clone)],
            ]));
        }

        if (!$this->isCloningAcrossApps()) {
            $this->assignKeptRelationships();
        }
        $this->cloneDuplicateRelationships();
        if ($this->isTranslatable()) {
            $this->cloneTranslations();
        }
        $this->clone->save();

        return $this->clone;
    }

    /**
     * Chainable method to set if this cloning process is a child/dependee of
     * an existing parent cloning process.
     *
     * @param boolean $isChildProcess
     * @return Duplicator
     */
    public function setIsChildProcess(bool $isChildProcess): Duplicator
    {
        $this->_isChildProcess = $isChildProcess;
        return $this;
    }

    /**
     * Is child/dependee process?
     *
     * @return boolean
     */
    protected function isChildProcess(): bool
    {
        return $this->_isChildProcess;
    }

    /**
     * Chainable method to set if this cloning process is restoring
     * the parent-chain for the original object
     *
     * @param bool $isCloningParentDependency
     * @return $this
     */
    public function setIsCloningParentDependency(bool $isCloningParentDependency): Duplicator
    {
        $this->_isCloningParentDependency = $isCloningParentDependency;
        return $this;
    }

    /**
     * Are we currently cloning a parent dependency of the original object?
     *
     * @return boolean
     */
    protected function IsCloningParentDependency(): bool
    {
        return $this->_isCloningParentDependency;
    }

    /**
     * Tries to get the clone that was created in a prior cloning process,
     * if allowed to do so, so it can be re-used.
     *
     * @return Model|null
     */
    private function getPreviousClone(): ?Model
    {
        if (!$this->cloneOnlyOnce()) {
            return null;
        }

        $cloneRecord = $this->_metadata['clonerecords']
            ->where('type', MorphTypes::MAPPING[get_class($this->original)])
            ->where('source_id', $this->original->id)
            ->first();
        if ($cloneRecord) {
            return $this->_metadata['clones']
                ->where('type', $cloneRecord->type)
                ->where('id', $cloneRecord->target_id)
                ->first()['model'];
        }

        $cloneRecord = CloneRecord::where('type', MorphTypes::MAPPING[get_class($this->original)])
            ->where('target_app_id', $this->isCloningAcrossApps() ? $this->_targetAppId : $this->original->app->id)
            ->where('source_id', $this->original->id)
            ->first();
        if (!$cloneRecord) {
            return null;
        }
        $previousClone = $this->original->find($cloneRecord->target_id);
        if (!$previousClone) {
            $cloneRecord->delete();
        }
        return $previousClone;
    }

    /**
     * Processes the properties of the cloned model,
     * unsetting the properties that should be removed,
     * overwriting the app id where applicable and
     * setting the new properties.
     *
     * @return void
     */
    private function setCloneProperties(): void
    {
        foreach($this->removeProperties() as $removeProperty) {
            unset($this->clone->{$removeProperty});
        }
        if ($this->isCloningAcrossApps() && isset($this->original->getAttributes()['app_id'])) {
            $this->clone->app_id = $this->_targetAppId;
        }
        foreach($this->setProperties() as $key => $value) {
            $this->clone->{$key} = $value;
        }
        $this->clone->save();
    }

    /**
     * Assigns the relationships that should be kept from
     * the original, re-using the existing dependencies and dependees.
     * This only has effect when cloning inside the same app.
     *
     * @return void
     */
    private function assignKeptRelationships(): void
    {
        $this->original->load(array_keys($this->keepRelationships()));
        $relations = $this->original->getRelations();
        foreach ($this->keepRelationships() as $relationship => $settings) {
            if (!isset($settings['pivotValues'])) {
                $this->clone->{$relationship}()->sync($relations[$relationship]);
            } else {
                $this->clone->{$relationship}()->syncWithPivotValues($relations[$relationship], $settings['pivotValues']);
            }
        }
    }

    /**
     * Creates new models for the relationships that are supposed to be cloned.
     *
     * @return void
     */
    private function cloneDuplicateRelationships(): void
    {
        $duplicateRelationships = $this->duplicateRelationships();
        $this->original->load(array_keys($duplicateRelationships));
        foreach ($duplicateRelationships as $relationship => $settings) {
            switch (get_class($this->original->{$relationship}())) {
                case HasMany::class:
                    foreach ($this->original->{$relationship} as $relatedModel) {
                        $relatedClone = $relatedModel->duplicateAsDependency($this->_targetAppId, $this->_metadata, $relationship);
                        $this->clone->{$relationship}()->save($relatedClone);
                    }
                    break;
                case BelongsTo::class:
                case MorphTo::class:
                    if ($this->original->{$relationship}) {
                        $relatedClone = $this->original->{$relationship}->duplicateAsDependency($this->_targetAppId, $this->_metadata, $relationship);
                        $relatedClone->save();
                        $this->clone->{$relationship}()->associate($relatedClone);
                    }
                    break;
                default:
                    throw new Exception('Can\'t handle relationship "' . $relationship . '"!');
            }
        }
    }

    /**
     * Clones the translation relationships.
     *
     * @return void
     */
    private function cloneTranslations(): void
    {
        if ($this->isCloningAcrossApps()) {
            $this->cloneTranslationsAcrossApps();
            return;
        }
        foreach ($this->original->allTranslationRelations as $translationModel) {
            $translationClone = $translationModel->duplicateAsDependency($this->_targetAppId, $this->_metadata, 'allTranslationRelations');
            $this->clone->allTranslationRelations()->save($translationClone);
        }
    }

    /**
     * Clones the translation relationships across apps.
     *
     * @return void
     */
    private function cloneTranslationsAcrossApps(): void
    {
        $targetDefaultLanguage = defaultAppLanguage($this->_targetAppId);
        foreach ($this->original->allTranslationRelations as $translationModel) {
            // only copy over those languages we actually use
            if (in_array($translationModel->language, App::getLanguagesById($this->_targetAppId))) {
                $translationClone = $translationModel->duplicateAsDependency($this->_targetAppId, $this->_metadata, 'allTranslationRelations');
                $this->clone->allTranslationRelations()->save($translationClone);
            }
        }
        $defaultTranslation = $this->clone->translationRelation($targetDefaultLanguage)->first();
        // we might be missing the default language now
        if (!$defaultTranslation) {
            // copy the source default to the target default
            $translationClone = $this->original
                ->setAppId($this->_metadata['source_app_id'])
                ->defaultTranslationRelation
                ->first()
                ->duplicateAsDependency($this->_targetAppId, $this->_metadata, 'allTranslationRelations');
            $translationClone->language = $targetDefaultLanguage;
            $this->clone->allTranslationRelations()->save($translationClone);
            self::$_warnings->push([
                'message' => get_class($this->original) . '#' . $this->original->id . ' has no translation for language ' . $targetDefaultLanguage,
                'type' => 'MissingDefaultLanguage',
            ]);
        } elseif ($targetDefaultLanguage != defaultAppLanguage($this->_metadata['source_app_id'])) {
            // if we're making a non-default translation the new default translation,
            // we need to manually copy over the fallbacks
            $originalDefaultTranslation = $this->original
                ->setAppId($this->_metadata['source_app_id'])
                ->defaultTranslationRelation
                ->first();
            foreach ($this->clone->translated as $attribute) {
                $defaultTranslation[$attribute] = $defaultTranslation[$attribute] ?: $originalDefaultTranslation[$attribute];
            }
            $defaultTranslation->save();
        }
    }

    /**
     * Returns the warnings from the last cloning process.
     *
     * @return Collection
     */
    public static function getWarnings(): Collection
    {
        return self::$_warnings ?? collect();
    }
}
