<?php

namespace App\Mail;

use App\Models\AppProfile;
use App\Models\Game;
use App\Models\User;
use App\Services\AppSettings;
use App\Services\QueuePriority;
use App\Traits\CustomMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class GameDrawInfo extends KeelearningNotification
{
    use Queueable, SerializesModels;
    use CustomMail;

    private User $opponent;
    private AppProfile $appProfile;

    public static array $availableTags = [
        'opponent',
        'gamelink',
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

        $this->appProfile = $user->getAppProfile();
        $appSettings = new AppSettings($game->app_id);

        $this->app = $game->app;
        $this->data = [
            'opponent' => $opponent->username,
            'gamelink' => $this->appProfile->app_hosted_at . ($appSettings->getValue('has_candy_frontend') ? $game->getCandyGamePath() : $game->getOldGamePath()),
        ];
        $this->queue = QueuePriority::MEDIUM;
        $this->recipient = $user;
        $this->opponent = $opponent;
        $this->pushNotificationMessage = __('notifications.game_draw_against', ['name' => $opponent->username], $user->getLanguage());
        $this->pushNotificationData = ['game_id' => $game->id, 'open_url' => '/quizzes/' . $game->id];
    }

    public function wantsEmailNotification(bool $isExternalRecipient = false): bool
    {
        if (!parent::wantsEmailNotification($isExternalRecipient)) {
            return false;
        }
        if (!$this->recipient->active) {
            return false;
        }
        if ($this->opponent->is_bot && !$this->appProfile->getValue('bot_game_mails')) {
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
        if ($this->opponent->is_bot && !$this->appProfile->getValue('bot_game_mails')) {
            return false;
        }

        return true;
    }
}
