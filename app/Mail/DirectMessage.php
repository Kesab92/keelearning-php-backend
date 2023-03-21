<?php

namespace App\Mail;

use App\Models\User;
use App\Services\QueuePriority;
use App\Traits\CustomMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class DirectMessage extends KeelearningNotification
{
    use Queueable, SerializesModels;
    use CustomMail;

    public static array $availableTags = [];
    public static array $requiredTags = [
        'message',
    ];
    protected bool $isAlwaysActive = true;

    /**
     * Create a new message instance.
     *
     * @param string $message
     * @param User $user
     */
    public function __construct(string $message, User $user)
    {
        parent::__construct();

        $this->app = $user->app;
        $this->data = [
            'message' => $message,
        ];
        $this->queue = QueuePriority::LOW;
        $this->recipient = $user;
        $this->pushNotificationMessage = __('notifications.direct_message', ['app-name' => $user->getAppProfile()->getValue('app_name')], $user->getLanguage());
        $this->pushNotificationData = ['open_url' => '/'];
    }

    public function getCustomViewData()
    {
        return [
            'hideEncoding' => true,
            'noNl2br' => true,
        ];
    }
}
