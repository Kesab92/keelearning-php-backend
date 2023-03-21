<?php

namespace App\Mail;

use App\Models\SuggestedQuestion;
use App\Models\User;
use App\Services\QueuePriority;
use App\Traits\CustomMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class AppQuestionSuggestion extends KeelearningNotification
{
    use Queueable, SerializesModels;
    use CustomMail;

    public static array $availableTags = [
        'question',
        'link',
        'user-send-message-link',
    ];
    public static array $requiredTags = [];
    protected bool $isAlwaysActive = true;

    /**
     * Create a new message instance.
     *
     * @param SuggestedQuestion $question
     * @param User $user
     */
    public function __construct(SuggestedQuestion $question, User $user)
    {
        parent::__construct();

        $this->app = $user->app;
        $this->data = [
            'question' => $question->title,
            'link' => backendPath() . '/suggested-questions#/suggested-questions',
            'user-send-message-link' => backendPath() . '/users#/users/' . $user->id . '/message',
        ];;
        $this->forceAppProfile = $user->getAppProfile();
        $this->queue = QueuePriority::LOW;
    }

    public function wantsEmailNotification(bool $isExternalRecipient = false): bool
    {
        if (!parent::wantsEmailNotification($isExternalRecipient)) {
            return false;
        }
        if (!$this->app->getNotificationMails()) {
            return false;
        }

        return true;
    }
}
