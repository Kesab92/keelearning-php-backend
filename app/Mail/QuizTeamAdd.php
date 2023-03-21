<?php

namespace App\Mail;

use App\Models\QuizTeam;
use App\Models\User;
use App\Services\QueuePriority;
use App\Traits\CustomMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class QuizTeamAdd extends KeelearningNotification
{
    use Queueable, SerializesModels;
    use CustomMail;

    public static array $availableTags = [
        'quiz-team-name',
    ];
    public static array $requiredTags = [];

    /**
     * Create a new message instance.
     *
     * @param QuizTeam $quizTeam
     * @param User $user
     */
    public function __construct(QuizTeam $quizTeam, User $user)
    {
        parent::__construct();

        $this->app = $user->app;
        $this->data = [
            'quiz-team-name' => $quizTeam->name,
        ];
        $this->queue = QueuePriority::LOW;
        $this->recipient = $user;
        $this->pushNotificationMessage = __('notifications.added_to_quiz_team', ['quizteamname' => $quizTeam->name], $user->getLanguage());
    }
}
