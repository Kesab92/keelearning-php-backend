<?php

namespace App\Services;

class QueuePriority
{
    /**
     * Defines the queue priority as low.
     */
    const LOW = 'low';

    /**
     * Defines the queue priority as medium/default.
     */
    const MEDIUM = 'default';

    /**
     * Defines the queue priority as high.
     */
    const HIGH = 'high';
}
