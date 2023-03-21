<?php

namespace App\Samba\Data;

use Carbon\Carbon;

class UpdateSession
{
    private int $sambaId;
    private string $topic;
    private $duration;
    private bool $allDay = false;
    private Carbon $startTime;
    private string $description;
    private int $sambaCustomerId;

    public function getData()
    {
        return [
            'id' => $this->sambaId,
            'user_id' => $this->sambaCustomerId,
            'topic' => $this->topic,
            'duration' => $this->duration,
            'start_time' => $this->startTime->format('Y-m-d H:i:s'),
            'description' => $this->description,
            'advanced_mode' => true,
            'all_day' => $this->allDay,
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
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @param int $sambaId
     */
    public function setSambaId(int $sambaId): void
    {
        $this->sambaId = $sambaId;
    }

    /**
     * @param int $sambaCustomerId
     */
    public function setSambaCustomerId(int $sambaCustomerId): void
    {
        $this->sambaCustomerId = $sambaCustomerId;
    }
}
