<?php

namespace App\Mail;

use App\Models\App;
use App\Models\Competition;
use App\Models\User;
use App\Services\QueuePriority;
use App\Traits\CustomMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class CompetitionResult extends KeelearningNotification
{
    use Queueable, SerializesModels;
    use CustomMail;

    public static array $availableTags = [
        'position',
        'title',
        'category',
        'rightanswers',
        'overview',
        'players',
    ];
    public static array $requiredTags = [];

    /**
     * Create a new message instance.
     *
     * @param int $position
     * @param int $playerCount
     * @param int $rightAnswers
     * @param Competition $competition
     * @param User $user
     * @param App $app
     */
    public function __construct(int $position, int $playerCount, int $rightAnswers, Competition $competition, User $user, App $app)
    {
        parent::__construct();

        $this->app = $app;
        $this->data = [
            'position' => $position,
            'category' => $competition->getCategoryName(),
            'rightanswers' => $rightAnswers,
            'players' => $playerCount,
            'title' => $competition->title,
        ];;
        $this->queue = QueuePriority::LOW;
        $this->recipient = $user;
        $this->pushNotificationMessage = __('notifications.competition_has_ended', ['title' => $competition->title], $user->getLanguage());
        $this->pushNotificationData = ['open_url' => '/competitions/' . $competition->id];
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
}
