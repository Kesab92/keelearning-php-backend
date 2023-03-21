<?php

namespace App\Events;

use App\Models\Game;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class GameChatMessage extends Event implements ShouldBroadcast
{
    /**
     * @var
     */
    private $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [$this->message['c']];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return $this->message;
    }
}
