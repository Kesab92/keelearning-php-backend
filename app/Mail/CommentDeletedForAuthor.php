<?php

namespace App\Mail;

use App\Models\Comments\Comment;
use App\Models\User;
use App\Services\QueuePriority;
use App\Traits\CustomMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class CommentDeletedForAuthor extends KeelearningNotification
{
    use Queueable, SerializesModels;
    use CustomMail;

    public static array $availableTags = [
        'admin-justification',
    ];
    public static array $requiredTags = [
        'admin-action',
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
            'admin-action' => __('notifications.comment_has_been_deleted'),
            'admin-justification' => $statusExplanation,
        ];
        $this->recipient = $user;
        $this->queue = QueuePriority::LOW;
        $this->pushNotificationMessage = __('notifications.your_comment_was_deleted', ['comment-date' => $comment->created_at->toDateTimeString('minute')], $user->getLanguage());
        $this->pushNotificationData = ['open_url' => $comment->getContentFrontendUrl()];
    }

    public function getCustomViewData()
    {
        return [
            'hideEncoding' => true,
        ];
    }
}
