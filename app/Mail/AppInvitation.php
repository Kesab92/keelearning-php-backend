<?php

namespace App\Mail;

use App\Models\App;
use App\Models\User;
use App\Services\QueuePriority;
use App\Traits\CustomMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class AppInvitation extends KeelearningNotification
{
    use Queueable, SerializesModels;
    use CustomMail;

    public static array $availableTags = ['app-id'];
    public static array $requiredTags = [
        'password',
    ];
    protected bool $isAlwaysActive = true;

    /**
     * Create a new message instance.
     *
     * @param string $password
     * @param User $user
     * @param App $app
     */
    public function __construct(string $password, User $user, App $app)
    {
        parent::__construct();

        $appProfile = $user->getAppProfile();
        $this->app = $app;
        $this->data = [
            'password' => $password,
            'app-id' => $appProfile->getValue('slug'),
        ];
        $this->queue = QueuePriority::HIGH;
        $this->recipient = $user;
    }
}
