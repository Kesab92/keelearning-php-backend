<?php

namespace App\Models\Comments;

use App\Models\KeelearningModel;

/**
 * Class CommentAttachment
 *
 * @mixin IdeHelperCommentAttachment
 */
class CommentAttachment extends KeelearningModel
{
    const ATTACHMENT_COUNT_LIMIT = 10;
    /**
     * 10 MB in bytes
     */
    const ATTACHMENT_FILESIZE_LIMIT = 10 * 1024 * 1024;

    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }
}
