<?php

namespace App\Mail;

use App\Models\Comments\Comment;
use App\Models\User;
use App\Services\QueuePriority;
use App\Traits\CustomMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class CommentNotDeleted extends KeelearningNotification
{
    use Queueable, SerializesModels;
    use CustomMail;

    public static array $availableTags = [];
    public static array $requiredTags = [
        'admin-action',
        'admin-justification',
        'comment-date',
        'comment',
    ];

    /**
     * Create a new message instance.
     *
     * @param string|null $statusExplanation
     * @param Comment $comment
     * @param User $user
     */
    public function __construct(?string $statusExplanation, Comment $comment, User $user)
    {
        parent::__construct();

        $this->app = $user->app;
        $this->data = [
            'comment' => $comment->body,
            'comment-date' => $comment->created_at->toDateTimeString('minute'),
            'admin-action' => __('notifications.comment_has_not_been_deleted'),
            'admin-justification' => $statusExplanation,
        ];;
        $this->recipient = $user;
        $this->queue = QueuePriority::LOW;
        $this->pushNotificationMessage = __('notifications.reported_comment_was_not_deleted', [], $user->getLanguage());
        $this->pushNotificationData = ['open_url' => $comment->getContentFrontendUrl()];
    }
}
