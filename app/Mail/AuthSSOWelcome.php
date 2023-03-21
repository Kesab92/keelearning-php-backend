<?php

namespace App\Mail;

use App\Models\User;
use App\Services\QueuePriority;
use App\Traits\CustomMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class AuthSSOWelcome extends KeelearningNotification
{
    use CustomMail;
    use Queueable;
    use SerializesModels;

    public static array $availableTags = ['app-id'];
    public static array $requiredTags = [];
    protected bool $isAlwaysActive = true;

    /**
     * Create a new message instance.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        parent::__construct();

        $appProfile = $user->getAppProfile();
        $this->app = $user->app;
        $this->data = [
            'app-id' => $appProfile->getValue('slug'),
        ];
        $this->queue = QueuePriority::HIGH;
        $this->recipient = $user;
    }
}
