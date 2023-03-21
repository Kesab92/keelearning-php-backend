<?php

namespace App\Removers;

use App\Removers\RemovalResult;
use DB;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Remover.
 */
class Remover
{
    protected Model $object;

    public function __construct($object)
    {
        $this->object = $object;
    }

    /**
     * Tries to delete the object, checking if so is possible.
     *
     * @return RemovalResult
     * @throws \Exception
     */
    public function delete()
    {
        if ($this->canBeDeleted() === true) {
            DB::transaction(function () {
                $this->doDeletion();
            });

            return new RemovalResult(true);
        } else {
            $messages = $this->getBlockingDependees();

            return new RemovalResult(false, $messages);
        }
    }

    /**
     * Executes the actual deletion.
     *
     * @return boolean
     * @throws \Exception
     */
    protected function doDeletion()
    {
        $this->deleteDependees();
        $this->object->delete();

        return true;
    }

    /**
     * Deletes the object's dependees.
     */
    protected function deleteDependees()
    {
    }

    /**
     * Checks if the object can be deleted.
     *
     * @return bool
     */
    public function canBeDeleted()
    {
        if ($this->getBlockingDependees() === false) {
            return true;
        }

        return false;
    }

    /**
     * Checks if any dependees are preventing deletion of this object.
     *
     * @return mixed false if clear of blocking dependees, array of strings if not
     */
    public function getBlockingDependees()
    {
        return false;
    }

    /**
     * Gets amount of dependees that will be deleted/altered.
     *
     * @return mixed `false` if clear of dependees, associative array with `'model' => $count` if not
     */
    public function getDependees()
    {
        return false;
    }
}
