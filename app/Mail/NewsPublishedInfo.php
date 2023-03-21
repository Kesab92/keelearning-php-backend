<?php

namespace App\Mail;

use App\Models\News;
use App\Models\User;
use App\Services\QueuePriority;
use App\Traits\CustomMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class NewsPublishedInfo extends KeelearningNotification
{
    use Queueable, SerializesModels;
    use CustomMail;

    protected $user = null;
    public static array $availableTags = [
        'news-link',
        'news-title',
        'news-excerpt',
    ];
    public static array $requiredTags = [];

    /**
     * NewsPublishedInfo constructor.
     * @param News $news
     * @param User $user
     */
    public function __construct(News $news, User $user)
    {
        parent::__construct();

        $news->setLanguage($user->getLanguage());

        $appProfile = $user->getAppProfile();
        $url = $appProfile->app_hosted_at . '/news/' . $news->id;
        $link = '<a href="' . $url . '">' . $news->title . '</a>';

        $this->data = [
            'news-link' => $url,
            'news-title' => $link,
            'news-excerpt' => $news->getExcerpt(),
        ];;
        $this->app = $user->app;
        $this->queue = QueuePriority::LOW;
        $this->recipient = $user;
        $this->pushNotificationMessage = __('notifications.news_published', [], $user->getLanguage());
        $this->pushNotificationData = ['open_url' => '/news/' . $news->id];
    }

    public function getCustomViewData()
    {
        return [
            'hideEncoding' => true,
        ];
    }
}
