<?php

namespace App\Samba\Data;

class LeaveSession
{
    private int $sessionId;
    private int $inviteeId;

    public function getData()
    {
        return [
            'session_id' => $this->sessionId,
            'user_id' => $this->inviteeId,
        ];
    }

    /**
     * @param int $sessionId
     */
    public function setSessionId(int $sessionId): void
    {
        $this->sessionId = $sessionId;
    }

    /**
     * @param int $inviteeId
     */
    public function setInviteeId(int $inviteeId): void
    {
        $this->inviteeId = $inviteeId;
    }
}
