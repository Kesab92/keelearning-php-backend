<?php

namespace App\Mail;

use App\Models\TestSubmission;
use App\Models\User;
use App\Services\AppSettings;
use App\Services\QueuePriority;
use App\Traits\CustomMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class TestPassed extends KeelearningNotification
{
    use Queueable, SerializesModels;
    use CustomMail;

    public static array $availableTags = [
        'test_name',
        'passed_date',
        'passed_percentage',
        'certificate_link',
    ];
    public static array $requiredTags = [];

    /**
     * Create a new message instance.
     *
     * @param TestSubmission $testSubmission
     * @param User $user
     */
    public function __construct(TestSubmission $testSubmission, User $user)
    {
        parent::__construct();

        $testSubmission->test->setLanguage($user->getLanguage());

        $appSettings = new AppSettings($user->app_id);
        $appProfile = $user->getAppProfile();
        $certificateLink = $appProfile->app_hosted_at . '/tests/' . $testSubmission->test->id . '/done/' . $testSubmission->id;

        if ($appSettings->getValue('has_candy_frontend')) {
            $certificateLink = $appProfile->app_hosted_at . '/tests/' . $testSubmission->test->id . '/submissions/' . $testSubmission->id;
        }

        $this->app = $testSubmission->test->app;
        $this->data = [
            'test_name' => $testSubmission->test->name,
            'passed_date' => $testSubmission->updated_at->format('d.m.Y'),
            'passed_percentage' => $testSubmission->percentage(),
            'certificate_link' => $certificateLink,
        ];
        $this->queue = QueuePriority::HIGH;
        $this->recipient = $user;
        $this->pushNotificationMessage = __('notifications.test_passed', ['name' => $testSubmission->test->name, 'percentage' => $testSubmission->percentage()], $user->getLanguage());
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
