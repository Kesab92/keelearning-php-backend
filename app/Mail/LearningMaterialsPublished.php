<?php

namespace App\Mail;

use App\Models\LearningMaterial;
use App\Models\User;
use App\Services\AppSettings;
use App\Services\QueuePriority;
use App\Traits\CustomMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class LearningMaterialsPublished extends KeelearningNotification
{
    use Queueable, SerializesModels;
    use CustomMail;

    public static array $availableTags = [
        'material-title',
        'material-link',
        'title', // legacy title, unlinked
    ];
    public static array $requiredTags = [];

    /**
     * NewsPublishedInfo constructor.
     * @param LearningMaterial $learningMaterial
     * @param User $user
     */
    public function __construct(LearningMaterial $learningMaterial, User $user)
    {
        parent::__construct();

        $learningMaterial->setLanguage($user->getLanguage());

        $appSettings = new AppSettings($learningMaterial->learningMaterialFolder->app_id);
        $appProfile = $user->getAppProfile();

        $url = $appProfile->app_hosted_at . '/learningpaths/material/' . $learningMaterial->id;
        if ($appSettings->getValue('has_candy_frontend')) {
            $url = $appProfile->app_hosted_at . '/learningmaterials/' . $learningMaterial->id;
        }

        $link = '<a href="' . $url . '">' . $learningMaterial->title . '</a>';

        $this->data = [
            'material-link' => $url,
            'material-title' => $link,
            'title' => $learningMaterial->title, // legacy
        ];
        $this->app = $user->app;
        $this->queue = QueuePriority::LOW;
        $this->recipient = $user;
        $this->pushNotificationMessage = __('notifications.learning_materials_published', [], $user->getLanguage());
        $this->pushNotificationData = ['open_url' => '/learningmaterials/' . $learningMaterial->id];
    }

    public function getCustomViewData()
    {
        return [
            'hideEncoding' => true,
        ];
    }
}
