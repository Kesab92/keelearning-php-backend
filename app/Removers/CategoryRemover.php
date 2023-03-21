<?php

namespace App\Removers;

use App\Models\Competition;
use App\Models\Game;
use App\Models\SuggestedQuestion;
use App\Models\Test;
use App\Removers\Remover;

class CategoryRemover extends Remover
{
    /**
     * Deletes/Resets everything depending on the category.
     */
    protected function deleteDependees()
    {
        $this->object->questions()->update([
            'category_id' => null,
            'visible' => false,
        ]);
        $this->object->indexCards()->update([
            'category_id' => null,
        ]);
        $this->object->suggestedQuestions()->each(function ($entry) {
            $entry->safeRemove();
        });
        $this->object->competitions()->each(function ($entry) {
            $entry->safeRemove();
        });
        $this->object->tags()->detach();

        // delete games depending on the deleted category
        $id = $this->object->id;
        $games = Game::whereHas('gamerounds', function ($query) use ($id) {
            $query->where('game_rounds.category_id', $id);
        })
                       ->get();
        foreach ($games as $game) {
            $game->safeRemove();
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
        $this->object->deleteImage();
        $this->object->deleteIcon();
        $this->object->delete();

        return true;
    }

    /**
     * Gets amount of dependees that will be deleted/altered.
     *
     * @return false if clear of dependees, array of strings if not
     */
    public function getDependees()
    {
        $id = $this->object->id;

        return [
            'suggestions' => SuggestedQuestion::where('category_id', $id)->count(),
            'competitions' => Competition::where('category_id', $id)->count(),
            'games' => Game::whereHas('gamerounds', function ($query) use ($id) {
                $query->where('game_rounds.category_id', $id);
            })->count(),
            'runningGames' => Game::where('status', '>', 0)
                                   ->whereHas('gamerounds', function ($query) use ($id) {
                                       $query->where('game_rounds.category_id', $id);
                                   })->count(),
        ];
    }

    /*
     * Checks if any tests or competitions have this group as dependency
     *
     * @return false if clear of blocking dependees, array of strings if not
     */
    public function getBlockingDependees()
    {
        $messages = [];
        $id = $this->object->id;
        $count = Game::where('status', '>', 0)
                    ->whereHas('gamerounds', function ($query) use ($id) {
                        $query->where('game_rounds.category_id', $id);
                    })->count();
        if ($count) {
            $messages[] = 'Laufende Spiele: '.$count;
        }
        $tests = Test::whereHas('testCategories', function ($query) use ($id) {
            $query->where('category_id', $id);
        })->get();
        foreach ($tests as $test) {
            $messages[] = 'Test: '.$test->name.' - bitte die Kategorie aus dem Test entfernen.';
        }
        foreach($this->object->questions as $question) {
            $blockingQuestionDependees = $question->getBlockingDependees();
            if($blockingQuestionDependees) {
                foreach($blockingQuestionDependees as $blocker) {
                    $messages[] = 'Frage "' . $question->title . '": ' . $blocker;
                }
            }
        }

        return count($messages) ? $messages : false;
    }
}
