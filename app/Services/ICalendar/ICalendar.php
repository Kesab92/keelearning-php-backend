<?php

namespace App\Services\ICalendar;

use App\Services\ICalendar\ContentTypes\ContentInterface;
use Illuminate\Support\Collection;

abstract class ICalendar
{
    protected Collection $rows;

    public function __construct(ContentInterface $content) {
        $this->rows = collect([]);
        $this->attachBeginVariable('VCALENDAR');
        $this->rows->push(
            'VERSION:2.0',
            'CALSCALE:GREGORIAN',
        );
        $this->attachContent($content);
        $this->attachEndVariable('VCALENDAR');
    }

    /**
     * Returns the content in iCalendar format
     * @return string
     */
    public function getContent(): string
    {
        return $this->rows->implode("\n");
    }


    /**
     * Attaches beginning of the variable
     * @param string $variable
     * @return void
     */
    protected function attachBeginVariable(string $variable) {
        $this->rows->push('BEGIN:' . $variable);
    }

    /**
     * Attaches ending of the variable
     * @param string $variable
     * @return void
     */
    protected function attachEndVariable(string $variable) {
        $this->rows->push('END:' . $variable);
    }

    /**
     * Attaches the content
     * @param ContentInterface $content
     * @return void
     */
    protected function attachContent(ContentInterface $content) {}
}
