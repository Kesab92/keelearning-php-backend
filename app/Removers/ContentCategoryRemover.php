<?php

namespace App\Removers;

class ContentCategoryRemover extends Remover
{
    protected function deleteDependees()
    {
        $this->object->allTranslationRelations()->delete();
        $this->object->contentCategoryRelations()->delete();
    }
}
