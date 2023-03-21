<?php

namespace App\Mail;

use App\Models\Reporting;
use App\Services\AppSettings;
use App\Services\QueuePriority;
use App\Services\Users\UsersStatsExport;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;

class UserReporting extends KeelearningNotification
{
    use Queueable, SerializesModels;

    private $recipientEmail;
    private $tags;
    private $interval;
    private $csv;
    protected bool $isAlwaysActive = true;

    /**
     * Create a new message instance.
     *
     * @param Reporting $reporting
     * @param string $recipientEmail
     * @param $tags
     * @param string $interval
     */
    public function __construct(Reporting $reporting, string $recipientEmail, $tags, string $interval)
    {
        parent::__construct();

        $appSettings = new AppSettings($reporting->app_id);
        $hasPersonalData = !$appSettings->getValue('hide_personal_data');
        $hasPersonalDataExternal = $hasPersonalData && !$appSettings->getValue('hide_personal_data_for_external_users');
        $export = new UsersStatsExport($appSettings, $reporting->tag_ids, null, true, $hasPersonalDataExternal, $hasPersonalDataExternal);

        $this->app = $reporting->app;
        $this->recipientEmail = $recipientEmail;
        $this->tags = $tags;
        $this->interval = $interval;
        $this->csv = base64_encode(Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX));
        $this->queue = QueuePriority::LOW;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $content = view('mail.reporting', [
            'email' => $this->recipientEmail,
            'tags' => $this->tags,
            'interval' => $this->interval,
            'app' => $this->app,
        ])->render();

        return $this->view('mail.html')
            ->with([
                'content' => $content,
                'app' => $this->app,
                'language' => $this->app->getLanguage(),
                'appProfile' => $this->app->getDefaultAppProfile(),
            ])
            ->attachData(base64_decode($this->csv), 'report-benutzer-' . date('Y-m-d') . '.xlsx', [
                'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])
            ->subject('Reporting ' . $this->app->app_name . ', Stand: ' . date('d.m.Y'));
    }
}
