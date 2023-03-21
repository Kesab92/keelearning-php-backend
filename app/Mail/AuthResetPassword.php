<?php

namespace App\Mail;

use App\Models\User;
use App\Services\QueuePriority;
use App\Traits\CustomMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class AuthResetPassword extends KeelearningNotification
{
    use Queueable, SerializesModels;
    use CustomMail;

    public static array $availableTags = [];
    public static array $requiredTags = [
        'password',
    ];
    protected bool $isAlwaysActive = true;

    /**
     * Create a new message instance.
     *
     * @param string $password
     * @param User $user
     */
    public function __construct(string $password, User $user)
    {
        parent::__construct();

        $this->app = $user->app;
        $this->data = [
            'password' => $password,
        ];;
        $this->queue = QueuePriority::HIGH;
        $this->recipient = $user;
    }
}
