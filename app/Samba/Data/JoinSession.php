<?php

namespace App\Samba\Data;

use App\Models\WebinarAdditionalUser;
use Illuminate\Support\Str;

class JoinSession
{
    private int $sessionId;
    private string $email;
    private int $role = WebinarAdditionalUser::ROLE_PARTICIPANT;
    private string $firstName = ' ';
    private string $lastName = ' ';

    public function getData()
    {
        return [
            'session_id' => $this->sessionId,
            'email' => $this->email,
            'role' => $this->role,
            'first_name' => caseSensitiveSlug($this->firstName, ' ', 'de') ?: ' ',
            'last_name' => caseSensitiveSlug($this->lastName, ' ', 'de') ?: ' ',
            'send_email_invitation' => 0, // We don't want samba to send any mails, because we want to send our own
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
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @param int $role
     */
    public function setRole(int $role): void
    {
        $this->role = $role;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }
}
