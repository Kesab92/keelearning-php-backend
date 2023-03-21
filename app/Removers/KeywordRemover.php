<?php

namespace App\Removers;

class KeywordRemover extends Remover
{
    protected function deleteDependees()
    {
        $this->object->categories()->detach();
        $this->object->allTranslationRelations()->delete();
    }
}
