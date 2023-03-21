<?php

namespace App\Mail;

use App\Models\User;
use App\Services\QueuePriority;
use App\Traits\CustomMail;
use App\Traits\HasAppointmentTags;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class EmailChangeConfirmation extends KeelearningNotification
{
    use Queueable, SerializesModels;
    use CustomMail;
    use HasAppointmentTags;

    public static array $availableTags = [];
    public static array $requiredTags = [
        'new-email',
        'confirmation-link',
    ];
    protected bool $isAlwaysActive = true;

    /**
     * EmailChangeConfirmation constructor.
     * @param string $newEmail
     * @param User $user
     */
    public function __construct(string $newEmail, User $user)
    {
        parent::__construct();

        $this->app = $user->app;
        $this->data = [
            'new-email' => $newEmail,
            'confirmation-link' => URL::signedRoute('emailChangeConfirm', ['userId' => $user->id, 'email' => $newEmail], now()->addMinutes(30)),
        ];
        $this->queue = QueuePriority::LOW;
        $this->recipient = $user;
    }
}
