<?php

namespace App\Traits;

use App\Removers\RemovalResult;
use App\Removers\Remover;

/**
 * Trait Saferemovable.
 */
trait Saferemovable
{
    private Remover $removerInstance;

    public function __construct(array $attributes = [])
    {
        $className = explode('\\', get_class());
        $className = '\\App\\Removers\\'.end($className).'Remover';
        $this->removerInstance = new $className($this);

        return parent::__construct($attributes);
    }

    /**
     * Tries to safely remove the object.
     * This method will check if an object can be deleted,
     * and do so, if possible; cleanup included.
     *
     * @return RemovalResult
     * @throws \Exception
     */
    public function safeRemove(): RemovalResult
    {
        return $this->removerInstance->delete();
    }

    /**
     * Gets an array of dependencies that'll be deleted.
     *
     * @return mixed array of dependencies, or false if no dependencies found
     */
    public function safeRemoveDependees()
    {
        return $this->removerInstance->getDependees();
    }

    /**
     * Returns a boolean value if this object can be deleted.
     * @return bool
     */
    public function canBeDeleted(): bool
    {
        return $this->removerInstance->canBeDeleted();
    }

    /**
     * Returns the reason why an object cant be deleted,
     * or false, if it's deletable.
     */
    public function getBlockingDependees()
    {
        return $this->removerInstance->getBlockingDependees();
    }
}
