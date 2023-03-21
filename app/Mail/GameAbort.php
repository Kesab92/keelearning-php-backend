<?php

namespace App\Mail;

use App\Models\Game;
use App\Models\User;
use App\Services\QueuePriority;
use App\Traits\CustomMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class GameAbort extends KeelearningNotification
{
    use Queueable, SerializesModels;
    use CustomMail;

    public static array $availableTags = [
        'opponent',
    ];
    public static array $requiredTags = [];

    /**
     * Create a new message instance.
     *
     * @param Game $game
     * @param User $opponent
     * @param User $user
     */
    public function __construct(Game $game, User $opponent, User $user)
    {
        parent::__construct();

        $this->app = $game->app;
        $this->data = [
            'opponent' => $opponent->username,
        ];;
        $this->queue = QueuePriority::MEDIUM;
        $this->recipient = $user;
        $this->pushNotificationMessage = __('notifications.game_aborted_against', ['name' => $opponent->username], $user->getLanguage());
        $this->pushNotificationData = ['open_url' => '/quizzes/' . $game->id];
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
