<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Webinar;
use App\Services\AppSettings;
use App\Services\QueuePriority;
use App\Traits\CustomMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class WebinarReminder extends KeelearningNotification
{
    use Queueable, SerializesModels;
    use CustomMail;

    public static array $availableTags = [
        'webinar-link',
        'webinar-topic',
        'webinar-description',
    ];
    public static array $requiredTags = [];

    /**
     * @param Webinar $webinar
     * @param User $user
     */
    public function __construct(Webinar $webinar, User $user)
    {
        parent::__construct();

        $appSettings = new AppSettings($webinar->app_id);
        $appProfile = $user->getAppProfile();
        $url = $appProfile->app_hosted_at . '/webinar/' . $webinar->id;

        if ($appSettings->getValue('has_candy_frontend')) {
            $url = $appProfile->app_hosted_at . '/webinars/' . $webinar->id;
        }

        $link = '<a href="' . $url . '">' . $webinar->topic . '</a>';

        $this->data = [
            'webinar-description' => $webinar->description,
            'webinar-link' => $url,
            'webinar-topic' => $link,
        ];
        $this->app = $user->app;
        $this->queue = QueuePriority::LOW;
        $this->recipient = $user;
        $this->pushNotificationMessage = __('notifications.webinar_reminder', [], $user->getLanguage());
        $this->pushNotificationData = ['open_url' => '/webinar/' . $webinar->id];
    }

    public function getCustomViewData()
    {
        return [
            'hideEncoding' => true,
        ];
    }
}
