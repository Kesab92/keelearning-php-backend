<?php

namespace App\Mail;

use App\Models\User;
use App\Services\QueuePriority;
use App\Traits\CustomMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class AppFeedback extends KeelearningNotification
{
    use Queueable, SerializesModels;
    use CustomMail;

    public static array $availableTags = [
        'user-send-message-link',
    ];
    public static array $requiredTags = [
        'feedback-subject',
        'feedback-message',
    ];
    protected bool $isAlwaysActive = true;

    /**
     * Create a new message instance.
     *
     * @param string $subject
     * @param string $message
     * @param User $user
     */
    public function __construct(string $subject, string $message, User $user)
    {
        parent::__construct();

        $this->app = $user->app;
        $this->data = [
            'feedback-subject' => $subject,
            'feedback-message' => $message,
            'user-send-message-link' => backendPath() . '/users#/users/' . $user->id . '/message',
        ];
        $this->forceAppProfile = $user->getAppProfile();
        $this->queue = QueuePriority::LOW;
        $this->recipient = $user;
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
