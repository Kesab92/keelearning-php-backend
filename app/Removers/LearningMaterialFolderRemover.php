<?php

namespace App\Removers;

use App\Models\LearningMaterial;
use App\Models\LearningMaterialFolder;
use App\Services\LearningMaterialEngine;

class LearningMaterialFolderRemover extends Remover
{
    /**
     * Deletes/Resets everything depending on the learning material.
     */
    protected function deleteDependees()
    {
        foreach ($this->object->learningMaterials as $learningMaterial) {
            /* @var LearningMaterial $learningMaterial */
            $learningMaterial->safeRemove();
        }
        foreach ($this->object->childFolders as $child) {
            /* @var LearningMaterialFolder $child */
            $child->safeRemove();
        }

        /** @var LearningMaterialEngine $learningMaterialEngine */
        $learningMaterialEngine = app(LearningMaterialEngine::class);
        $learningMaterialEngine->removeLearningMaterialFolderIcon($this->object);
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
     * Checks if anything has this folder or it's contents as dependency
     *
     * @return false if clear of blocking dependees, array of strings if not
     */
    public function getBlockingDependees()
    {
        $messages = [];

        $messages = array_merge($messages, $this->getLearningmaterialMessages());

        foreach($this->object->childFolders as $child) {
            /** @var LearningMaterialFolder $child */
            $childBlockers = $child->getBlockingDependees();
            if($childBlockers) {
                $messages = array_merge($messages, $childBlockers);
            }
        }

        if (count($messages) > 0) {
            return $messages;
        } else {
            return false;
        }
    }

    private function getLearningmaterialMessages()
    {
        $messages = [];

        foreach ($this->object->learningMaterials as $learningMaterial) {
            /** @var LearningMaterial $learningMaterial */
            $learningMaterialMessages = $learningMaterial->getBlockingDependees();
            if ($learningMaterialMessages) {
                $messages = array_merge($messages, $learningMaterialMessages);
            }
        }

        return $messages;
    }

    /**
     * Gets amount of dependees that will be deleted/altered.
     */
    public function getDependees()
    {
        $childFolders = $this->object->childFolders;
        $childFolders->load('translationRelation');
        $dependees = [
            'Dateien' => $this->object->learningMaterials->load('translationRelation')->pluck('title'),
            'Ordner' => $childFolders->pluck('name'),
        ];
        foreach($childFolders as $child) {
            /** @var LearningMaterialFolder $child */
            $childDependees = $child->safeRemoveDependees();
            $dependees['Dateien'] = $dependees['Dateien']->merge($childDependees['Dateien']);
            $dependees['Ordner'] = $dependees['Ordner']->merge($childDependees['Ordner']);
        }

        return $dependees;
    }
}
