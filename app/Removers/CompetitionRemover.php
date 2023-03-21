<?php

namespace App\Removers;

use App\Models\Comments\CommentReport;
use App\Models\Competition;

class CompetitionRemover extends Remover
{
    /**
     * Deletes the competition's tags.
     */
    protected function deleteDependees()
    {
        /** @var Competition $competition */
        $competition = $this->object;

        CommentReport::whereIn('comment_id', $competition->comments->pluck('id'))->delete();
        $competition->comments()->delete();

        $competition->tags()->detach();
    }

    /**
     * Gets amount of dependees that will be deleted/altered
     *
     * @return boolean|array false if clear of dependees, array of counts if not
     */
    public function getDependees()
    {
        /** @var Competition $competition */
        $competition = $this->object;

        $comments = $competition->comments->count();
        $commentReports = CommentReport::whereIn('comment_id', $competition->comments->pluck('id'))->count();

        return [
            'Kommentare' => $comments,
            'Kommentar Meldungen' => $commentReports,
        ];
    }
}
