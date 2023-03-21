<?php

namespace App\Mail;

use App\Exports\DefaultExport;
use App\Models\Reporting;
use App\Models\Tag;
use App\Models\User;
use App\Services\AppSettings;
use App\Services\QueuePriority;
use App\Services\StatsEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;

class QuizReporting extends KeelearningNotification
{
    use Queueable, SerializesModels;

    private $recipientEmail;
    private $groups;
    private $tags;
    private $interval;
    private $csv;
    protected bool $isAlwaysActive = true;

    /**
     * Create a new message instance.
     *
     * @param Reporting $reporting
     * @param string $recipientEmail
     * @param Tag[] $tags
     * @param string $interval
     * @throws \Exception
     */
    public function __construct(Reporting $reporting, string $recipientEmail, $tags, string $interval)
    {
        parent::__construct();

        $appSettings = new AppSettings($reporting->app_id);
        $hasPersonalData = !$appSettings->getValue('hide_personal_data');
        $hasPersonalDataExternal = $hasPersonalData && !$appSettings->getValue('hide_personal_data_for_external_users');
        $players = $this->gatherData($reporting);

        $this->app = $reporting->app;
        $this->recipientEmail = $recipientEmail;
        $this->tags = $tags;
        $this->interval = $interval;;
        $this->csv = base64_encode($this->getExcelString($players, $appSettings, $hasPersonalDataExternal));
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
            ->attachData(base64_decode($this->csv), 'report-lernstatistik-' . date('Y-m-d') . '.xlsx', [
                'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])
            ->subject('Reporting Lernstatistik ' . $this->app->app_name . ', Stand: ' . date('d.m.Y'));
    }

    /**
     * Fetches the current player data.
     *
     * @param Reporting $reporting
     * @return User[]
     * @throws \Exception
     */
    private function gatherData(Reporting $reporting)
    {
        $statsEngine = new StatsEngine($reporting->app_id);

        return $statsEngine->getFilteredPlayersList($reporting->tag_ids, $reporting->category_ids);
    }

    /**
     * Returns an excel file as string which contains the given data.
     *
     * @param $players
     * @param AppSettings $settings
     * @param bool $showPersonalData
     * @return mixed
     */
    private function getExcelString($players, AppSettings $settings, bool $showPersonalData)
    {
        $tagGroups = $settings->getApp()->tagGroups;
        $tags = $settings->getApp()->tags->pluck('label', 'id');

        $data = [
            'showPersonalData' => $showPersonalData,
            'showEmails' => $showPersonalData,
            'showIp' => $settings->getValue('save_user_ip_info'),
            'players' => $players,
            'tagGroups' => $tagGroups,
            'tags' => $tags,
            'appSettings' => $settings,
            'metaFields' => $settings->getApp()->getUserMetaDataFields($showPersonalData),
        ];

        return Excel::raw(new DefaultExport($data, 'stats.quiz.csv.players'), \Maatwebsite\Excel\Excel::XLSX);
    }
}
