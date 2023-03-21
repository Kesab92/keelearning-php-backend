<?php

namespace App\Removers;

use Session;

class RemovalResult
{
    public $success = false;
    public $messages = [];

    public function __construct($success = false, $messages = [])
    {
        $this->success = $success;
        $this->messages = $messages;
    }

    public function isSuccessful() {
        return $this->success;
    }
    
    /*
     * Flashes the messages with the supplied type & message prefix
     *
     */
    public function flashMessages($type, $message)
    {
        $message .= implode('<br>', $this->messages);
        Session::flash($type, $message);
    }

    /**
     * Returns all messages.
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
