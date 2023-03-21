<?php

namespace App\Mail;

use App\Models\User;
use App\Services\QueuePriority;
use App\Traits\CustomMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class UserDeletionRequest extends KeelearningNotification
{
    use Queueable, SerializesModels;
    use CustomMail;

    public static array $availableTags = [
        'user-id',
        'user-send-message-link',
    ];
    public static array $requiredTags = [
        'user-profile-link',
    ];
    protected bool $isAlwaysActive = true;

    /**
     * UserDeletionRequest constructor.
     * @param User $user
     */
    public function __construct(User $user)
    {
        parent::__construct();

        $this->app = $user->app;
        $this->data = [
            'user-id' => $user->id,
            'user-profile-link' => 'https://admin.keelearning.de/users#/users/' . $user->id . '/general',
            'user-send-message-link' => 'https://admin.keelearning.de/users#/users/' . $user->id . '/message',
        ];
        $this->queue = QueuePriority::LOW;
        $this->recipient = $user;
    }
}
