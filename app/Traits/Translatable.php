<?php

namespace App\Traits;

use App\Models\App;
use App\Models\TranslationStatus;
use App\Observers\TranslatableObserver;
use App\Services\MorphTypes;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Sentry;

/**
 * Class Translatable.
 *
 * For the translation migration to work,
 * the Translations need their own Models
 * and the parent Model (with the translatable trait)
 * needs to expose an `app_id` attribute.
 *
 * @property \Eloquent $translationRelation
 */
trait Translatable
{
    private $_translationCache = [];
    private $_translationFallbackCache = null;
    private $_forceLanguage = null;
    private $_forceAppId = null;
    private $_disableFallback = false;

    public function getAppId()
    {
        if ($this->_forceAppId) {
            return $this->_forceAppId;
        }
        return $this->app_id;
    }

    public function translationLanguage()
    {
        return $this->_forceLanguage ?: language($this->getAppId());
    }

    protected static function bootTranslatable()
    {
        static::observe(TranslatableObserver::class);
    }

    public static function withTranslation()
    {
        return self::with('translationRelation');
    }

    public function translationRelation($lc = null)
    {
        if (is_null($lc)) {
            $lc = $this->translationLanguage();
        }

        return $this->hasMany($this->getTranslationModelName())->where($this->getTranslationTableName().'.language', $lc);
    }

    public function defaultTranslationRelation()
    {
        $lc = defaultAppLanguage($this->getAppId());

        return $this->hasMany($this->getTranslationModelName())->where($this->getTranslationTableName().'.language', $lc);
    }

    public function allTranslationRelations()
    {
        return $this->hasMany($this->getTranslationModelName());
    }

    /**
     * Returns the translation for this model.
     *
     * @param null $lc Falls back to the default locale
     * @param boolean $useFallback If set to false, we will always return the active language and not fall back to the default language if the requested language doesn't exist
     *
     * @return mixed
     */
    public function translation($lc = null, bool $useFallback = true)
    {
        if (is_null($lc)) {
            $lc = $this->translationLanguage();
        }
        $defaultLanguage = defaultAppLanguage($this->getAppId());
        // See if we can use the preloaded translation
        // FIXME: fallback when neither current nor default language is set does not yet work with preloaded translation relations
        if ($lc == $this->translationLanguage() && $this->relationLoaded('translationRelation')) {
            if($this->translationRelation->first()) {
                return $this->translationRelation->first();
            }
            if(!$useFallback) {
                return null;
            }
            // Use the preloaded default translation as fallback
            if($this->relationLoaded('defaultTranslationRelation') && $this->defaultTranslationRelation->first()) {
                return $this->defaultTranslationRelation->first();
            }
            return $this->translationRelation(defaultAppLanguage($this->getAppId()))->first();
        }

        if($lc == $defaultLanguage) {
            if($this->relationLoaded('defaultTranslationRelation') && $this->defaultTranslationRelation->first()) {
                return $this->defaultTranslationRelation->first();
            }
        }

        // Cache the translation if it isn't cached already
        if (! isset($this->_translationCache[$lc])) {
            $this->_translationCache[$lc] = $this->translationRelation($lc)->first();
        }
        if ($this->_translationCache[$lc]) {
            return $this->_translationCache[$lc];
        }

        if(!$useFallback) {
            return null;
        }

        $defaultLanguage = defaultAppLanguage($this->getAppId());
        if (! isset($this->_translationCache[$defaultLanguage])) {
            $this->_translationCache[$defaultLanguage] = $this->translationRelation($defaultLanguage)->first();
        }
        if ($this->_translationCache[$defaultLanguage]) {
            return $this->_translationCache[$defaultLanguage];
        }
        if (! $this->_translationFallbackCache) {
            $this->_translationFallbackCache = $this->allTranslationRelations()->first();
        }

        return $this->_translationFallbackCache;
    }

    public function isTranslated($lc = null)
    {
        if (is_null($lc)) {
            $lc = $this->translationLanguage();
        }

        return (bool) $this->translationRelation($lc)->first();
    }

    /**
     * Returns the raw translation/translation attribute,
     * without falling back to defaults.
     */
    public function getRawTranslation($attribute = null)
    {
        if (! $relation = $this->translationRelation($this->translationLanguage())->first()) {
            return null;
        }
        if ($attribute) {
            return $relation->{$attribute};
        }

        return $relation;
    }

    /**
     * Returns the name of the translation model.
     *
     * @return string
     */
    public function getTranslationModelName()
    {
        return self::class.'Translation';
    }

    /**
     * Returns the name of the translation table.
     *
     * @return string
     */
    public function getTranslationTableName()
    {
        return Str::singular($this->getTable()).'_translations';
    }

    public function getForeignIdColumn()
    {
        $className = explode('\\', self::class);

        return Str::snake(array_pop($className)).'_id';
    }

    private function saveTranslatedAttribute($attribute, $value)
    {
        $lang = $this->translationLanguage();
        $translation = array_key_exists($lang, $this->_translationCache) ? $this->_translationCache[$lang] : $this->translationRelation($lang)->first();
        $defaultLang = defaultAppLanguage($this->getAppId());
        if (! $translation) {
            if ($lang != $defaultLang) {
                if (!$this->translationRelation($defaultLang)->count()) {
                    // we could just force the default language here...
                    Sentry::captureException(new Exception('Created model without translation in default language: ' . get_class($this)));
                }
            }
            $modelName = $this->getTranslationModelName();
            $translation = new $modelName();
            $translation->{$this->getForeignIdColumn()} = $this->id;
            $translation->language = $lang;
            $this->translationRelation->push($translation);
        } else {
            $appId = $this->getAppId();
            // In some instances we don't have the correct app id.
            // As this feature is somewhat optional, we try to use the currently active app id and just do nothing when we don't have it available.
            if(!$appId) {
                try {
                    $appId = appId();
                } catch (\Exception $e) {}
            }
            if($translation->{$attribute} !== $value && $appId && $this->exists) {
                if(!isset(MorphTypes::MAPPING[get_class($this)])) {
                    $message = 'No morph type defined for class ' . get_class($this);
                    Sentry::captureMessage($message);
                    logger($message);
                } else {
                    $type = MorphTypes::MAPPING[get_class($this)];
                    $this->updateCurrentLanguageTranslationStatus($appId, $lang, $type, $attribute);

                    if($lang === $defaultLang) {
                        // If the translation already exists, and it's the default translation, mark the other translations as outdated.
                        $userId = null;
                        try {
                            $userId = Auth::user()->id;
                        } catch (\Exception $e) {}

                        $languages = App::getLanguagesById($appId);
                        TranslationStatus::unguard();
                        foreach($languages as $language) {
                            if($language === $defaultLang) {
                                continue;
                            }
                            $status = TranslationStatus::firstOrCreate([
                                'app_id' => $appId,
                                'language' => $language,
                                'foreign_id' => $this->id,
                                'foreign_type' => $type,
                            ]);
                            $fieldStatuses = $status->field_statuses;
                            if(!isset($fieldStatuses[$attribute])) {
                                $fieldStatuses[$attribute] = [];
                            }
                            $fieldStatuses[$attribute]['is_outdated'] = true;
                            $fieldStatuses[$attribute]['original_updated_at'] = Carbon::now();
                            $fieldStatuses[$attribute]['last_updated_by_id'] = $userId;
                            $status->field_statuses = $fieldStatuses;
                            $status->is_outdated = true;
                            $status->last_updated_by_id = $userId;
                            $status->save();
                        }
                        TranslationStatus::reguard();
                    }
                }
            }
        }

        $translation->{$attribute} = $value;
        $this->_translationCache[$lang] = $translation;

        if ($this->id) {
            $translation->save();
        }
    }

    private function getTranslatedAttribute($attribute)
    {
        $translation = $this->translation();
        if (
            $translation
            && $translation->{$attribute} !== null
            && $translation->{$attribute} !== ''
        ) {
            return $translation->{$attribute};
        }

        $translation = $this->translation(defaultAppLanguage($this->getAppId()), true);
        if (!$translation) {
            throw new Exception('Missing default translation: ' . get_class($this) . ' #' . $this->id);
        }
        return $translation->{$attribute};
    }

    public function saveTranslationRelation()
    {
        foreach ($this->_translationCache as $translation) {
            $translation->{$this->getForeignIdColumn()} = $this->id;
            $translation->save();
        }
    }

    public function deleteAllTranslations()
    {
        $modelName = $this->getTranslationModelName();
        $modelName::where($this->getForeignIdColumn(), $this->id)->delete();
    }

    public function getAttribute($key)
    {
        if (in_array($key, $this->translated)) {
            if($this->_disableFallback) {
                return $this->getRawTranslation($key);
            }
            return $this->getTranslatedAttribute($key);
        }

        return parent::getAttribute($key);
    }

    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->translated)) {
            return $this->saveTranslatedAttribute($key, $value);
        }

        return parent::setAttribute($key, $value);
    }

    public function cacheTranslation($object)
    {
        $this->_translationCache[$object->language] = $object;
    }

    public function __call($name, $args)
    {
        if (preg_match('/get(.*?)Attribute/', $name, $match)) {
            $attribute = Str::snake($match[1]);
            if (in_array($attribute, $this->translated)) {
                return $this->getAttribute($attribute);
            }
        }

        return parent::__call($name, $args);
    }

    protected function getArrayableAppends()
    {
        return array_merge(parent::getArrayableAppends(), $this->translated);
    }

    public function setLanguage($lang)
    {
        $this->_forceLanguage = $lang;
        return $this;
    }

    public function setAppId($appId)
    {
        $this->_forceAppId = $appId;
        return $this;
    }

    public function disableTranslationFallback()
    {
        $this->_disableFallback = true;
        return $this;
    }


    public static function orderByTranslatedField($query, $field, $appId, $direction = 'asc')
    {
        $dummyModel = (new static);
        $modelTable = $dummyModel->getTable();
        $translationTable = $dummyModel->getTranslationTableName();
        $language = language($appId);
        $defaultLanguage = defaultAppLanguage($appId);
        $foreignKeyColumn = $dummyModel->getForeignIdColumn();

        if ($language == $defaultLanguage) {
            return $query->select($modelTable . '.*', 'translationRelation.' . $field)
                ->leftJoin(DB::raw($translationTable . ' as translationRelation'), function ($query) use ($foreignKeyColumn, $language, $modelTable) {
                    $query->on($modelTable . '.id', '=', 'translationRelation.' . $foreignKeyColumn)
                        ->where('translationRelation.language', $language);
                })->orderBy('translationRelation.' . $field, $direction);
        }

        return $query->select($modelTable . '.*', DB::raw('COALESCE(translationRelation.' . $field . ', translationRelationDefault.' . $field . ') as sortingField'))
            ->leftJoin(DB::raw($translationTable . ' as translationRelation'), function ($query) use ($foreignKeyColumn, $language, $modelTable) {
                $query->on($modelTable . '.id', '=', 'translationRelation.' . $foreignKeyColumn)
                    ->where('translationRelation.language', $language);
            })
            ->leftJoin(DB::raw($translationTable . ' as translationRelationDefault'), function ($query) use ($foreignKeyColumn, $defaultLanguage, $modelTable) {
                $query->on($modelTable . '.id', '=', 'translationRelationDefault.' . $foreignKeyColumn)
                    ->where('translationRelationDefault.language', $defaultLanguage);
            })
            ->orderBy('sortingField', $direction);
    }

    private function updateCurrentLanguageTranslationStatus(int $appId, string $language, int $type, string $attribute) {
        $userId = null;
        try {
            $userId = Auth::user()->id;
        } catch (\Exception $e) {}

        $status = TranslationStatus::firstOrCreate([
            'app_id' => $appId,
            'language' => $language,
            'foreign_id' => $this->id,
            'foreign_type' => $type,
        ]);
        $fieldStatuses = $status->field_statuses;
        if(!isset($fieldStatuses[$attribute])) {
            $fieldStatuses[$attribute] = [];
        }
        $fieldStatuses[$attribute]['is_autotranslated'] = false;
        $fieldStatuses[$attribute]['is_outdated'] = false;
        $fieldStatuses[$attribute]['updated_at'] = Carbon::now();
        $fieldStatuses[$attribute]['last_updated_by_id'] = $userId;
        $status->field_statuses = $fieldStatuses;
        $status->is_autotranslated = false;
        $status->is_outdated = false;
        $status->last_updated_by_id = $userId;
        $status->save();
    }
}
