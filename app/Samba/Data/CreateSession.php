<?php

namespace App\Samba\Data;

use Carbon\Carbon;

class CreateSession
{
    private string $topic;
    private $duration;
    private bool $allDay = false;
    private Carbon $startTime;
    private int $keeunitId;
    private string $description;
    private int $sambaCustomerId;

    public function getData()
    {
        return [
            'user_id' => $this->sambaCustomerId,
            'topic' => $this->topic,
            'duration' => $this->duration,
            'start_time' => $this->startTime->format('Y-m-d H:i:s'),
            'custom_data' => [
                'custom1' => $this->keeunitId,
            ],
            'advanced_mode' => true,
            'all_day' => $this->allDay,
            'description' => $this->description,
            'type' => 'html5',
            'private_access' => true,
            'av_encrypt_streams' => true,
            'register_require' => 0,
            'access_enable_breakout_rooms' => true,
            'menu_buttons' => [
                'screensharing' => true,
                'inviteparticipants' => false,
                'medialibrary' => true,
                'camera' => true,
                'options' => true,
                'recording' => true,
                'notifications' => true,
                'microphone' => true,
                'pushtotalk' => true,
                'requesttospeak' => true,
                'help' => false,
            ],
        ];
    }

    /**
     * @param string $topic
     */
    public function setTopic(string $topic): void
    {
        $this->topic = $topic;
    }

    /**
     * @param int $duration
     */
    public function setDuration($duration): void
    {
        if (! $duration) {
            $duration = 365 * 24 * 60;
            $this->allDay = true;
        }
        $this->duration = $duration;
    }

    /**
     * @param Carbon $startTime
     */
    public function setStartTime(Carbon $startTime): void
    {
        $this->startTime = $startTime;
    }

    /**
     * @param int $keeunitId
     */
    public function setKeeunitId(int $keeunitId): void
    {
        $this->keeunitId = $keeunitId;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @param int $sambaCustomerId
     */
    public function setSambaCustomerId(int $sambaCustomerId): void
    {
        $this->sambaCustomerId = $sambaCustomerId;
    }
}
