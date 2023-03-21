<?php

namespace App\Removers;

class WebinarRemover extends Remover
{
    /**
     * Deletes/Resets everything depending on the webinar.
     *
     * @throws \Exception
     */
    protected function deleteDependees()
    {
        $this->object->tags()->detach();
        $this->object->additionalUsers()->delete();
        $this->object->participants()->delete();
    }

    /**
     * Executes the actual deletion.
     *
     * @return true
     * @throws \Exception
     */
    protected function doDeletion()
    {
        $this->deleteDependees();
        $this->object->delete();

        return true;
    }
}
