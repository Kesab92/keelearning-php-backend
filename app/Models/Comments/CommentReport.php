<?php

namespace App\Models\Comments;

use App\Models\KeelearningModel;
use App\Models\User;

/**
 * Class CommentReport
 *
 * @package App\Models\Comments
 * @property int $id
 * @property int $reporter_id
 * @property int $comment_id
 * @property int $status_manager_id
 * @property int $reason
 * @property string $reason_explanation
 * @property int $status
 * @property string $status_explanation
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property Comment $comment
 * @property User $reporter
 * @property User $statusManager
 * @method static \Illuminate\Database\Eloquent\Builder|CommentReport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CommentReport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CommentReport query()
 * @method static \Illuminate\Database\Eloquent\Builder|CommentReport whereCommentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommentReport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommentReport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommentReport whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommentReport whereReasonExplanation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommentReport whereReporterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommentReport whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommentReport whereStatusExplanation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommentReport whereStatusManagerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommentReport whereUpdatedAt($value)
 * @mixin \Eloquent
 * @mixin IdeHelperCommentReport
 */
class CommentReport extends KeelearningModel
{
    const STATUS_REPORTED = 0;
    const STATUS_PROCESSED_UNJUSTIFIED = 1;
    const STATUS_PROCESSED_JUSTIFIED = 2;

    const REASON_MISC = 0;
    const REASON_OFFENSIVE = 1;
    const REASON_ADVERTISEMENT = 2;
    const REASON_PERSONAL_RIGHTS = 3;

    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class);
    }

    public function statusManager()
    {
        return $this->belongsTo(User::class);
    }
}
