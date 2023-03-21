<?php

namespace App\Mail;

use App\Models\User;
use App\Services\AccountActivation;
use App\Services\QueuePriority;
use App\Traits\CustomMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class AuthWelcome extends KeelearningNotification
{
    use Queueable, SerializesModels;
    use CustomMail;

    public static array $availableTags = [
        'account-activation-link',
        'app-id',
    ];
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
        $data = [
            'app-id' => $appProfile->getValue('slug'),
        ];
        if ($this->app->needsAccountActivation()) {
            /** @var AccountActivation $accountActivation */
            $accountActivation = app(AccountActivation::class);
            $data['account-activation-link'] = $accountActivation->getActivationLink($user);
        }

        $this->data = $data;
        $this->queue = QueuePriority::HIGH;
        $this->recipient = $user;
    }
}
