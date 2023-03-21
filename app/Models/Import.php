<?php

namespace App\Models;

use Cache;

/**
 * The `progress` and `steps` fields are quite special. Note that the progress isn't saved in the database but in the cache.
 * 
 * `steps` is an int and defines how many steps there are to do in an import (example for users: checking for validity, creating tags, creating users)
 * `progress` is a float and defines the current progress within a step, so a progress of 2.57 would mean that step 3 is 57% done.
 * 
 * Class Import
 *
 * @property int $id
 * @property int $app_id
 * @property int $creator_id
 * @property int $type
 * @property int $steps
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\App $app
 * @property-read \App\Models\User $creator
 * @method static \Illuminate\Database\Eloquent\Builder|Import newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Import newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Import query()
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereCreatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereSteps($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereUpdatedAt($value)
 * @mixin IdeHelperImport
 */
class Import extends KeelearningModel
{
    const TYPE_USERS_IMPORT = 0;
    const TYPE_USERS_DELETE = 1;
    const TYPE_QUESTIONS = 2;
    const TYPE_INDEXCARDS = 3;

    const STATUS_INPROGRESS = 0;
    const STATUS_DONE = 1;
    const STATUS_FAILED = 2;

    private $_progress = null;

    public function app()
    {
        return $this->belongsTo(App::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class);
    }

    private function getProgressCacheKey()
    {
        return 'import-progress-'.$this->id;
    }

    public function setProgress($progress)
    {
        $this->_progress = $progress;
        Cache::put($this->getProgressCacheKey(), $progress, 60 * 60 * 24 * 3);
    }

    public function getProgress()
    {
        if ($this->_progress !== null) {
            return $this->_progress;
        }

        return floatval(Cache::get($this->getProgressCacheKey(), 0));
    }
}
