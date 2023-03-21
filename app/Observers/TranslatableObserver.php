<?php

namespace App\Observers;

class TranslatableObserver
{
    public function saved($instance)
    {
        $instance->saveTranslationRelation();
    }

    public function deleted($instance)
    {
        $instance->deleteAllTranslations();
    }
}
