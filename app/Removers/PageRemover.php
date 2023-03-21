<?php

namespace App\Removers;

use App\Models\AppProfileSetting;
use App\Models\Page;

class PageRemover extends Remover
{
    protected function deleteDependees()
    {
        $this->object->allTranslationRelations()->delete();
    }

    /*
     * Checks if anything has this page as dependency
     *
     * @return false if clear of blocking dependees, array of strings if not
     */
    public function getBlockingDependees()
    {
        $messages = [];
        $id = $this->object->id;

        $existingSubPagesCount = Page::where('parent_id', $id)
            ->count();
        $termOfServiceCount = AppProfileSetting
            ::where('key', 'tos_id')
            ->where('value', $id)
            ->count();

        if ($existingSubPagesCount) {
            $messages[] = 'Diese Seite hat noch ' . $existingSubPagesCount . ' Sub-Seiten. Bitte weisen sie den Sub-Seiten erst eine neue Seite zu oder löschen Sie sie.';
        }
        if ($termOfServiceCount) {
            $messages[] = 'Diese Seite wird aktuell als Nutzungsbedingungen verwendet. Bitte weisen Sie in den Einstellungen erste eine andere Seite zu.';
        }

        if (count($messages) > 0) {
            return $messages;
        } else {
            return false;
        }
    }
}
