<?php

namespace App\Mail;

use App\Models\Competition;
use App\Models\User;
use App\Services\QueuePriority;
use App\Traits\CustomMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class CompetitionInvitation extends KeelearningNotification
{
    use Queueable, SerializesModels;
    use CustomMail;

    public static array $availableTags = [
        'category',
        'start',
        'end',
    ];
    public static array $requiredTags = [];

    /**
     * CompetitionInvitation constructor.
     * @param Competition $competition
     * @param User $user
     */
    public function __construct(Competition $competition, User $user)
    {
        parent::__construct();

        $categoryName = __('general.all_categories', [], $user->getLanguage());
        if ($competition->category) {
            $categoryName = $competition->category->setLanguage($user->getLanguage())->name;
        }

        $title = $competition->title;
        if (!$title) {
            $title = $categoryName;
        }

        $this->data = [
            'category' => $categoryName,
            'start' => $competition->start_at->format('d.m.Y H:i'),
            'end' => $competition->getEndDate()->format('d.m.Y H:i'),
        ];
        $this->app = $user->app;
        $this->queue = QueuePriority::LOW;
        $this->recipient = $user;
        $this->pushNotificationMessage = __('notifications.competition_start', ['name' => $title], $user->getLanguage());
    }
}
