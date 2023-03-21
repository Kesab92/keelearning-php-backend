<?php

namespace App\Mail;

use App\Models\Comments\Comment;
use App\Models\User;
use App\Services\QueuePriority;
use App\Traits\CustomMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use URL;

class SubscriptionComment extends KeelearningNotification
{
    use CustomMail, Queueable, SerializesModels;

    public static array $availableTags = [
        'comment-author',
        'comment-link',
        'comment-text',
        'content-link',
        'content-title',
        'unsubscribe-link',
    ];
    public static array $requiredTags = [];

    /**
     * @param int $contentType
     * @param int $contentId
     * @param Comment $newComment
     * @param int $userId
     */
    public function __construct(int $contentType, int $contentId, Comment $newComment, int $userId)
    {
        parent::__construct();

        $user = User::findOrFail($userId);
        if($user->is_admin) {
            $contentLink =  env('RELAUNCH_BACKEND_UI_URL') . $newComment->getContentBackendUrl();
            $commentLink = $contentLink;
        } else {
            $contentLink =  $user->getAppProfile()->app_hosted_at.$newComment->getContentFrontendUrl();
            $commentLink = $contentLink . '#comment-' . $newComment->id;
        }

        $unsubscribeUrl = URL::signedRoute('notification-subscriptions.unsubscribe', [
            'userId' => $user->id,
            'foreignId' => $contentId,
            'foreignType' =>$contentType,
        ]);

        $this->data = [
            'comment-author' =>  $newComment->author->displayname,
            'comment-text' =>  e($newComment->body),
            'comment-link' => '<a href="' . $commentLink . '">' . $commentLink . '</a>',
            'content-link' => '<a href="' . $contentLink . '">' . $contentLink . '</a>',
            'content-title' => '<a href="'.$contentLink . '">' . $newComment->getContentTitle() . '</a>',
            'unsubscribe-link' => '<a href="' . $unsubscribeUrl . '" style="font-size:70%;opacity:0.7">' . $unsubscribeUrl . '</a>',
        ];

        $this->app = $user->app;
        $this->queue = QueuePriority::LOW;
        $this->recipient = $user;
        $this->pushNotificationMessage = __('notifications.comment_reply', ['subcomment-author-name' => $newComment->author->displayname], $user->getLanguage());
        $this->pushNotificationData = ['open_url' =>$contentLink.'#comment-'.$newComment->id];
    }

    public function getCustomViewData()
    {
        return [
            'hideEncoding' => true,
        ];
    }

    public function wantsPushNotification(): bool
    {
        if($this->recipient->is_admin) {
            return false;
        }
        return parent::wantsPushNotification();
    }
}
