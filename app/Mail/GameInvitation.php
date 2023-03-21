<?php

namespace App\Mail;

use App\Models\App;
use App\Models\Game;
use App\Models\User;
use App\Services\AppSettings;
use App\Services\QueuePriority;
use App\Traits\CustomMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class GameInvitation extends KeelearningNotification
{
    use Queueable, SerializesModels;
    use CustomMail;

    public static array $availableTags = [
        'challenger',
        'gamelink',
    ];
    public static array $requiredTags = [];

    /**
     * Create a new message instance.
     *
     * @param User $player1
     * @param User $player2
     * @param Game $game
     * @param App $app
     */
    public function __construct(User $player1, User $player2, Game $game, App $app)
    {
        parent::__construct();

        $appSettings = new AppSettings($app->id);
        $appProfile = $player2->getAppProfile();
        $this->app = $app;
        $this->data = [
            'challenger' => $player1->username,
            'gamelink' => $appProfile->app_hosted_at . ($appSettings->getValue('has_candy_frontend') ? $game->getCandyGamePath() : $game->getOldGamePath()),
        ];
        $this->pushNotificationMessage = __('notifications.challenged_by', ['name' => $player1->username], $player2->getLanguage());
        $this->pushNotificationData = ['open_url' => '/quizzes/' . $game->id];
        $this->queue = QueuePriority::MEDIUM;
        $this->recipient = $player2;
    }
}
