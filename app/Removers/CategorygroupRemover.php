<?php

namespace App\Removers;

use App\Removers\Remover;

class CategorygroupRemover extends Remover
{
    /**
     * Deletes/Resets everything depending on the category.
     */
    protected function deleteDependees()
    {
        $this->object->categories()->update([
            'categorygroup_id' => null,
        ]);
        $this->object->tags()->detach();
    }

    /**
     * Executes the actual deletion.
     *
     * @return true
     */
    protected function doDeletion()
    {
        $this->deleteDependees();
        $this->object->delete();

        return true;
    }

    public function getDependees()
    {
        return [
            'categories' => $this->object->categories()->get()->pluck('name'),
        ];
    }

    public function getBlockingDependees()
    {
        $categories = $this->object->categories()->count();
        $messages = [];
        if ($categories) {
            $messages[] = 'Unterkategorien: '.$categories;
        }

        return count($messages) ? $messages : false;
    }
}
