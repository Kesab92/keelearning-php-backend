<?php

namespace App\Mail;

use App\Models\User;
use App\Services\QueuePriority;
use App\Traits\CustomMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class ItemFeedback extends KeelearningNotification
{
    use Queueable, SerializesModels;
    use CustomMail;

    public static array $availableTags = [
        'user-send-message-link',
    ];
    public static array $requiredTags = [
        'feedback-type',
        'feedback-link',
        'feedback-message',
    ];
    protected bool $isAlwaysActive = true;

    /**
     * Create a new message instance.
     *
     * @param string $message
     * @param string $type
     * @param string $url
     * @param User $user
     */
    public function __construct(string $message, string $type, string $url, User $user)
    {
        parent::__construct();

        $this->app = $user->app;
        $this->data = [
            'feedback-message' => $message,
            'feedback-type' => $type,
            'feedback-link' => $url,
            'user-send-message-link' => backendPath() . '/users#/users/' . $user->id . '/message',
        ];
        $this->forceAppProfile = $user->getAppProfile();
        $this->recipient = $user;
        $this->queue = QueuePriority::LOW;
    }

    public function wantsEmailNotification(bool $isExternalRecipient = false): bool
    {
        if (!parent::wantsEmailNotification($isExternalRecipient)) {
            return false;
        }
        if (!$this->app->getNotificationMails()) {
            return false;
        }

        return true;
    }
}
