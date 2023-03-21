<?php

namespace App\Traits;

use App\Duplicators\Duplicator;
use App\Models\CloneRecord;
use DB;
use Illuminate\Database\Eloquent\Model;
use Throwable;

/**
 * Trait Duplicatable.
 */
trait Duplicatable
{
    /**
     * Creates a clone of this model, wrapping the whole
     * cloning process in a single transaction.
     *
     * @param integer|null $targetAppId
     * @return Model
     */
    public function duplicate(?int $targetAppId = null): Model
    {
        // we want to wrap the whole duplication process in a single transaction,
        // including all spawned child duplication processes
        DB::beginTransaction();
        try {
            $duplicate = $this->getDuplicatorInstance($targetAppId)
                ->duplicate();
        } catch (Throwable $e) {
            DB::rollback();
            throw $e;
        }
        DB::commit();
        return $duplicate;
    }

    /**
     * Clones this model, but as a dependency of a parent
     * cloning process, not opening a new DB transaction.
     *
     * @param integer|null $targetAppId
     * @return Model
     */
    public function duplicateAsDependency(?int $targetAppId, array $metadata, string $relationship): Model
    {
        $duplicator = $this->getDuplicatorInstance($targetAppId);
        return $duplicator
            ->setIsChildProcess(true)
            ->setIsCloningParentDependency($duplicator->isParentDependency($relationship))
            ->setMetadata($metadata)
            ->duplicate();
    }

    /**
     * Creates a new instance of this model's
     * specialized duplicator class.
     *
     * @param integer|null $targetAppId
     * @return Duplicator
     */
    private function getDuplicatorInstance(?int $targetAppId): Duplicator
    {
        $className = explode('\\', get_class());
        $className = '\\App\\Duplicators\\'.end($className).'Duplicator';
        return (new $className($this))
            ->setTargetApp($targetAppId);
    }

    /**
     * Checks if this model is a clone which is marked for reuse in future cloning attemps
     *
     * @return Boolean
     */
    public function getIsReusableCloneAttribute(): bool
    {
        return !!$this->cloneRecord;
    }

    public function cloneRecord()
    {
        return $this->morphOne(CloneRecord::class, 'target', 'type');
    }
}
