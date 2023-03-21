<?php

namespace App\Mail;

use App\Models\User;
use App\Services\QueuePriority;
use App\Traits\CustomMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use URL;

class ExpirationReminder extends KeelearningNotification
{
    use Queueable, SerializesModels;
    use CustomMail{
        build as buildBase;
    }

    public static array $availableTags = [
        'certificate-links', // this will be populated on mail send
        'deletion-days', // days until deletion of account
    ];

    public static array $requiredTags = [];

    /**
     * Create a new message instance.
     *
     * @param int $deletionDays
     * @param User $user
     */
    public function __construct(int $deletionDays, User $user)
    {
        parent::__construct();

        $this->app = $user->app;
        $this->data = [
            'deletion-days' => $deletionDays,
        ];
        $this->queue = QueuePriority::LOW;
        $this->recipient = $user;
        $this->pushNotificationMessage = $this->getTitle();
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

    public function wantsPushNotification(): bool
    {
        if (!parent::wantsPushNotification()) {
            return false;
        }
        if (!$this->recipient->active) {
            return false;
        }

        return true;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->populateCertificateLinks();

        return $this->buildBase();
    }

    /**
     * Fetches the certificate links, if needed.
     */
    private function populateCertificateLinks()
    {
        if (!$this->containsTag('certificate-links')) {
            return;
        }
        $submissions = $this->recipient
            ->testSubmissions()
            ->with('test.certificateTemplates')
            ->where('result', true)
            ->whereHas('test', function ($query) {
                $query->where('no_download', false);
            })
            ->get();
        $certificateLinks = [];
        foreach ($submissions as $submission) {
            if ($submission->test->hasCertificateTemplate()) {
                $certificateLinks[] = URL::signedRoute('certificateDownload', [
                    'submission_id' => $submission->id,
                ]);
            }
        }
        $this->data['certificate-links'] = implode("\n", $certificateLinks);
    }
}
