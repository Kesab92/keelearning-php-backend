<?php

namespace App\Removers;

use App\Models\Courses\CourseContentAttachment;
use App\Models\Game;
use App\Models\QuestionAnswer;
use App\Models\QuestionAttachment;
use App\Models\Test;
use App\Models\TrainingAnswer;
use App\Removers\Traits\CourseDependencyMessage;
use App\Services\MorphTypes;
use Storage;

class QuestionRemover extends Remover
{
    use CourseDependencyMessage;

    /**
     * Deletes/Resets everything depending on the category.
     */
    protected function deleteDependees()
    {
        $id = $this->object->id;

        $this->object->questionAnswers()->delete();
        TrainingAnswer::where('question_id', $id)->delete();

        foreach ($this->object->attachments as $attachment) {
            if ($attachment->type != QuestionAttachment::ATTACHMENT_TYPE_YOUTUBE && QuestionAttachment::whereKeyNot($attachment->id)->where('attachment', $attachment->attachment)->doesntExist()) {
                Storage::delete($attachment->attachment);
            }
            $attachment->delete();
        }

        // delete games depending on the deleted question
        $games = Game::whereHas('gamerounds', function ($query) use ($id) {
            $query->whereHas('gamequestions', function ($query) use ($id) {
                $query->where('question_id', $id);
            });
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
        $this->object->delete();

        return true;
    }

    /*
     * Checks if anything has this question as dependency
     *
     * @return false if clear of blocking dependees, array of strings if not
     */
    public function getBlockingDependees()
    {
        $messages = [];
        $id = $this->object->id;

        $activeGamesCount = Game::join('game_rounds', 'game_rounds.game_id', '=', 'games.id')
            ->join('game_questions', 'game_questions.game_round_id', '=', 'game_rounds.id')
            ->where('question_id', $id)
            ->where('games.status', '>', 0)
            ->count();

        if ($activeGamesCount) {
            $messages[] = 'Laufende Spiele: ' . $activeGamesCount . ' - bitte die Frage deaktivieren und warten, bis laufende Spiele beendet wurden';
        }

        $tests = Test::whereHas('testquestions', function ($query) use ($id) {
            $query->where('question_id', $id);
        })->get();
        foreach ($tests as $test) {
            $messages[] = 'Test: ' . $test->name . ' - bitte die Frage aus dem Test entfernen.';
        }

        $courseContentAttachments = CourseContentAttachment::where('type', MorphTypes::TYPE_QUESTION)
            ->where('foreign_id', $id)
            ->with('content.course.translationRelation', 'content.translationRelation')
            ->get();
        foreach ($courseContentAttachments as $courseContentAttachment) {
            $messages[] = 'Kursinhalt "' . $courseContentAttachment->content->title .'" in Kurs "' . $courseContentAttachment->content->course->title . '" beinhaltet diese Frage';
        }

        $messages = array_merge($messages, $this->getCourseMessages(MorphTypes::TYPE_QUESTION, $this->object->id));

        if (count($messages) > 0) {
            return $messages;
        } else {
            return false;
        }
    }

    /**
     * Gets amount of dependees that will be deleted/altered.
     *
     * @return false if clear of blocking dependees, array of strings if not
     */
    public function getDependees()
    {
        $id = $this->object->id;

        $games = Game::join('game_rounds', 'game_rounds.game_id', '=', 'games.id')
            ->join('game_questions', 'game_questions.game_round_id', '=', 'game_rounds.id')
            ->where('question_id', $id)
            ->count();

        return [
            'games' => $games,
            'questionAnswers' => QuestionAnswer::where('question_id', $id)->count(),
            'trainingAnswers' => TrainingAnswer::where('question_id', $id)->count(),
            'questionAttachments' => QuestionAttachment::where('question_id', $id)->count(),
        ];
    }
}
