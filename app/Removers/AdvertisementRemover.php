<?php

namespace App\Removers;

use App\Models\Advertisements\AdvertisementTranslation;

class AdvertisementRemover extends Remover
{
    protected function deleteDependees()
    {
        $this->object->tags()->detach();
        $this->object->positions()->delete();
        $translations = $this->object->allTranslationRelations()->get();
        foreach ($translations as $translation) {
            if ($translation->rectangle_image_url && AdvertisementTranslation::whereKeyNot($translation->id)->where('rectangle_image_url', $translation->rectangle_image_url)->doesntExist()) {
                \Storage::delete(getBlobStorageFilename($translation->rectangle_image_url));
            }
            if ($translation->leaderboard_image_url && AdvertisementTranslation::whereKeyNot($translation->id)->where('leaderboard_image_url', $translation->leaderboard_image_url)->doesntExist()) {
                \Storage::delete(getBlobStorageFilename($translation->leaderboard_image_url));
            }
        }
        $this->object->allTranslationRelations()->delete();
    }
}
