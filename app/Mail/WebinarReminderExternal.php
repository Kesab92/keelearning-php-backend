<?php

namespace App\Mail;

use App\Models\WebinarAdditionalUser;
use App\Services\QueuePriority;
use App\Services\WebinarEngine;
use App\Traits\CustomMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class WebinarReminderExternal extends KeelearningNotification
{
    use Queueable, SerializesModels;
    use CustomMail;

    public static array $availableTags = [
        'webinar-user-name',
        'webinar-link',
        'webinar-topic',
        'webinar-description',
    ];
    public static array $requiredTags = [];
    protected bool $isAlwaysActive = true;

    /**
     * @param WebinarAdditionalUser $webinarAdditionalUser
     */
    public function __construct(WebinarAdditionalUser $webinarAdditionalUser)
    {
        parent::__construct();

        $webinarEngine = app(WebinarEngine::class);
        $url = $webinarEngine->getAdditionalUserJoinLink($webinarAdditionalUser);
        $link = '<a href="' . $url . '">' . $webinarAdditionalUser->webinar->topic . '</a>';

        $this->data = [
            'webinar-user-name' => $webinarAdditionalUser->name,
            'webinar-description' => $webinarAdditionalUser->webinar->description,
            'webinar-link' => $url,
            'webinar-topic' => $link,
        ];
        $this->app = $webinarAdditionalUser->webinar->app;
        $this->queue = QueuePriority::LOW;
    }

    public function getCustomViewData()
    {
        return [
            'hideEncoding' => true,
        ];
    }
}
