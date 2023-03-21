<?php

namespace App\Services\ICalendar;

use App\Services\ICalendar\ContentTypes\ContentInterface;

class IEvent extends ICalendar
{
    /**
     * Attaches the content
     * @param ContentInterface $content
     * @return void
     */
    protected function attachContent(ContentInterface $content) {
        $this->attachBeginVariable('VEVENT');

        if($content->createdAt) {
            $this->rows->push('DTSTAMP:' . $content->createdAt->format('Ymd\THis'));
        }
        if($content->startDate) {
            $this->rows->push('DTSTART:' . $content->startDate->format('Ymd\THis'));
        }
        if($content->endDate) {
            $this->rows->push('DTEND:' . $content->endDate->format('Ymd\THis'));
        }
        if($content->description) {
            $this->rows->push('DESCRIPTION:' . $content->description);
        }
        if($content->summary) {
            $this->rows->push('SUMMARY:' . $content->summary);
        }
        if($content->url) {
            $this->rows->push('URL:' . $content->url);
        }
        if($content->location) {
            $this->rows->push('LOCATION:' . $content->location);
        }
        if($content->status) {
            $this->rows->push('STATUS:' . $content->status);
        }

        $this->attachEndVariable('VEVENT');
    }
}
