<?php

namespace App\Services\ICalendar\ContentTypes;

use Illuminate\Support\Carbon;

class EventContent implements ContentInterface
{
    public Carbon $createdAt;
    public Carbon $endDate;
    public ?string $description;
    public ?string $location;
    public Carbon $startDate;
    public ?string $status = 'CONFIRMED';
    public ?string $summary;
    public Carbon $updatedAt;
    public ?string $url;

    /**
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param Carbon $createdAt
     * @param Carbon $updatedAt
     * @param string $status
     * @param string|null $description
     * @param string|null $summary
     * @param string|null $url
     * @param string|null $location
     */
    public function __construct(Carbon $startDate, Carbon $endDate, Carbon $createdAt, Carbon $updatedAt, string $status, ?string $description = null, ?string $summary = null, ?string $url = null, ?string $location = null)
    {
        $this->createdAt = $createdAt;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->description = $description;
        $this->location = $location;
        $this->updatedAt = $updatedAt;
        $this->status = $status;
        $this->summary = $summary;
        $this->url = $url;
    }


}
