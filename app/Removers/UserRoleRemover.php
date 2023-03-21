<?php

namespace App\Removers;

use App\Models\User;

class UserRoleRemover extends Remover
{
    protected function deleteDependees()
    {
        User::where('user_role_id', $this->object->id)
            ->update(['user_role_id' => null]);
        $this->object->rights()->delete();
    }

    /**
     * Checks if any tests or competitions have this group as dependency.
     *
     * @return false if clear of blocking dependees, array of strings if not
     */
    public function getBlockingDependees()
    {
        $messages = [];

        if($this->object->users()->exists()) {
            $messages[] = 'Benutzer: ' . $this->object->users()->count();
        }

        return count($messages) ? $messages : false;
    }


    public function canBeDeleted()
    {
        if ($this->object->is_main_admin) {
            return false;
        }

        return parent::canBeDeleted();
    }
}
