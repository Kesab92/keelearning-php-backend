<?php

namespace App\Removers;

  use App\Models\Competition;
  use App\Models\Test;
  use App\Removers\Remover;

class QuizTeamRemover extends Remover
{
    /*
   * Deletes the quizteam-user-pivot entries
   *
   */
    protected function deleteDependees()
    {
        $this->object->members()->detach();
    }

    /*
   * Checks if any tests or competitions have this quiz team as dependency
   *
   * @return false if clear of blocking dependees, array of strings if not
   */
    public function getBlockingDependees()
    {
        $messages = [];
        $competitions = Competition::where('quiz_team_id', $this->object->id)->get();
        foreach ($competitions as $competition) {
            $messages[] = 'Gewinnspiel: '.$competition->getCategoryName().', bis '.$competition->getEndDate()->format('d.m.Y H:i');
        }
        $tests = Test::where('quiz_team_id', $this->object->id)->get();
        foreach ($tests as $test) {
            $messages[] = 'Test: '.$test->name;
        }

        return count($messages) ? $messages : false;
    }
}
