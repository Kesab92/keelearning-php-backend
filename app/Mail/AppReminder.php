<?php

namespace App\Mail;

use App\Models\App;
use App\Models\User;
use App\Services\QueuePriority;
use App\Traits\CustomMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class AppReminder extends KeelearningNotification
{
    use Queueable, SerializesModels;
    use CustomMail;

    public static array $availableTags = [
        'position',
    ];

    public static array $requiredTags = [];

    /**
     * Create a new message instance.
     *
     * @param int $rankingNumber
     * @param User $user
     * @param App $app
     */
    public function __construct(int $rankingNumber, User $user, App $app)
    {
        parent::__construct();

        $this->app = $app;
        $this->data = [
            'position' => $rankingNumber,
        ];
        $this->queue = QueuePriority::LOW;
        $this->recipient = $user;
        $this->pushNotificationMessage = $this->getTitle();
    }

    public function wantsEmailNotification(bool $isExternalRecipient = false): bool
    {
        if (!parent::wantsEmailNotification($isExternalRecipient)) {
            return false;
        }
        if (!$this->recipient->active) {
            return false;
        }

        return true;
    }

    public function wantsPushNotification(): bool
    {
        if (!parent::wantsPushNotification()) {
            return false;
        }
        if (!$this->recipient->active) {
            return false;
        }

        return true;
    }
}
