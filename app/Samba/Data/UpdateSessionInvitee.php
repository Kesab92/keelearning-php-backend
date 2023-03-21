<?php

namespace App\Samba\Data;

use Illuminate\Support\Str;

class UpdateSessionInvitee
{
    private int $sessionId;
    private int $inviteeId;
    private string $email;
    private ?int $role = null;
    private string $firstName = ' ';
    private string $lastName = ' ';

    public function getData()
    {
        $data = [
            'session_id' => $this->sessionId,
            'user_id' => $this->inviteeId,
            'first_name' => caseSensitiveSlug($this->firstName, ' ', 'de') ?: ' ',
            'last_name' => caseSensitiveSlug($this->lastName, ' ', 'de') ?: ' ',
            'email' => $this->email,
            'send_email_invitation' => 0, // We don't want samba to send any mails, because we want to send our own
        ];

        if ($this->role !== null) {
            $data['role'] = $this->role;
        }

        return $data;
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

    /**
     * @param int $role
     */
    public function setRole(int $role): void
    {
        $this->role = $role;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }
}
