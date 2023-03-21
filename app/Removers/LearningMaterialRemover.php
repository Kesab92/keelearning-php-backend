<?php

namespace App\Removers;

use App\Models\Comments\CommentReport;
use App\Models\LearningMaterial;
use App\Removers\Traits\CourseDependencyMessage;
use App\Services\LearningMaterialEngine;
use App\Services\MorphTypes;
use Storage;

class LearningMaterialRemover extends Remover
{
    use CourseDependencyMessage;

    /**
     * Deletes/Resets everything depending on the learning material.
     */
    protected function deleteDependees()
    {
        /** @var LearningMaterial $learningMaterial */
        $learningMaterial = $this->object;
        /** @var LearningMaterialEngine $engine */
        $engine = app(LearningMaterialEngine::class);

        CommentReport::whereIn('comment_id', $learningMaterial->comments->pluck('id'))->delete();
        $learningMaterial->comments()->delete();

        if ($learningMaterial->cover_image) {
            if(LearningMaterial::whereKeyNot($learningMaterial->id)->where('cover_image', $learningMaterial->cover_image)->doesntExist()) {
                Storage::delete($learningMaterial->cover_image);
            }
        }
        $translations = $learningMaterial->allTranslationRelations()->get();
        foreach ($translations as $translation) {
            $engine->removeMedia($translation);
        }
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

    /*
     * Checks if anything has this material as dependency
     *
     * @return false if clear of blocking dependees, array of strings if not
     */
    public function getBlockingDependees()
    {
        $messages = [];

        $messages = array_merge($messages, $this->getCourseMessages(MorphTypes::TYPE_LEARNINGMATERIAL, $this->object->id));

        if (count($messages) > 0) {
            return $messages;
        } else {
            return false;
        }
    }

    /**
     * Gets amount of dependees that will be deleted/altered.
     */
    public function getDependees()
    {
        /** @var LearningMaterial $learningMaterial */
        $learningMaterial = $this->object;

        $comments = $learningMaterial->comments->count();
        $commentReports = CommentReport::whereIn('comment_id', $learningMaterial->comments->pluck('id'))->count();

        return [
            'Kommentare' => $comments,
            'Kommentar Meldungen' => $commentReports,
        ];
    }
}
