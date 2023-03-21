<?php

namespace App\Removers;

use App\Models\Comments\CommentReport;
use App\Models\News;

class NewsRemover extends Remover
{
    protected function deleteDependees()
    {
        /** @var News $newsEntry */
        $newsEntry = $this->object;

        CommentReport::whereIn('comment_id', $newsEntry->comments->pluck('id'))->delete();
        $newsEntry->comments()->delete();

        $newsEntry->tags()->detach();
        if ($newsEntry->cover_image_url && News::whereKeyNot($newsEntry->id)->where('cover_image_url', $newsEntry->cover_image_url)->doesntExist()) {
            \Storage::delete(getBlobStorageFilename($newsEntry->cover_image_url));
        }
        $newsEntry->allTranslationRelations()->delete();
    }

    /**
     * Gets amount of dependees that will be deleted/altered
     *
     * @return boolean|array false if clear of dependees, array of counts if not
     */
    public function getDependees()
    {
        /** @var News $newsEntry */
        $newsEntry = $this->object;

        $comments = $newsEntry->comments->count();
        $commentReports = CommentReport::whereIn('comment_id', $newsEntry->comments->pluck('id'))->count();

        return [
            'Kommentare' => $comments,
            'Kommentar Meldungen' => $commentReports,
        ];
    }
}
