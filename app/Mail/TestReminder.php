<?php

namespace App\Mail;

use App\Models\Test;
use App\Models\User;
use App\Services\QueuePriority;
use App\Traits\CustomMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class TestReminder extends KeelearningNotification
{
    use Queueable, SerializesModels;
    use CustomMail{
        build as buildBase;
    }

    public static array $availableTags = [
        'test',
        'active_until',
    ];
    public static array $requiredTags = [];

    /**
     * TestReminder constructor.
     * @param Test $test
     * @param User $user
     */
    public function __construct(Test $test, User $user)
    {
        parent::__construct();

        $test->setLanguage($user->getLanguage());

        $this->app = $user->app;
        $this->data = [
            'test' => $test->name,
            'testId' => $test->id,
            'active_until' => $test->active_until ? $test->active_until->format('d.m.Y H:i') : '-',
        ];
        $this->queue = QueuePriority::LOW;
        $this->recipient = $user;
        $this->pushNotificationMessage = __('notifications.test_reminder', $this->data, $user->getLanguage());
        $this->pushNotificationData = ['open_url' => '/tests/' . $test->id];
    }

    /**
     * Builds the mail.
     * @return TestReminder
     */
    public function build()
    {
        $this->withSwiftMessage(function ($message) {
            $message->user = $this->recipient;
            $message->testId = $this->data['testId'];
            $message->mailClass = self::class;
        });

        return $this->buildBase();
    }
}
