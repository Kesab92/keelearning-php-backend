<?php

namespace App\Mail;

use App\Models\Competition;
use App\Models\User;
use App\Services\QueuePriority;
use App\Traits\CustomMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class CompetitionReminder extends KeelearningNotification
{
    use Queueable, SerializesModels;
    use CustomMail;

    public static array $availableTags = [
        'competition-name',
        'days-running',
        'end-date',
        'position',
        'competition-link',
        'quiz-link',
    ];

    public static array $requiredTags = [];

    /**
     * Create a new message instance.
     *
     * @param int $rankingNumber
     * @param Competition $competition
     * @param User $user
     */
    public function __construct(int $rankingNumber, Competition $competition, User $user)
    {
        parent::__construct();

        $appProfile = $user->getAppProfile();
        $competitionLink = $appProfile->app_hosted_at . '/competition/' . $competition->id;
        $quizLink = $appProfile->app_hosted_at . '/create-game/';

        $this->app = $user->app;
        $this->data = [
            'competition-name' => $competition->title,
            'days-running' => $competition->start_at->diffInDays(),
            'end-date' => $competition->getEndDate(),
            'position' => $rankingNumber,
            'competition-link' => $competitionLink,
            'quiz-link' => $quizLink,
        ];
        $this->queue = QueuePriority::LOW;
        $this->recipient = $user;
        $this->pushNotificationMessage = $this->getTitle();
    }
}
