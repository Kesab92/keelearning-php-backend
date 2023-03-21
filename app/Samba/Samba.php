<?php

namespace App\Samba;

use App\Samba\Data\CreateAccount;
use App\Samba\Data\CreateSession;
use App\Samba\Data\JoinSession;
use App\Samba\Data\LeaveSession;
use App\Samba\Data\UpdateSession;
use App\Samba\Data\UpdateSessionInvitee;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * This class handles interaction with the Samba API.
 */
class Samba
{
    /**
     * @var SambaConnector
     */
    private SambaConnector $connector;

    /**
     * @var int
     */
    private int $sambaCustomerId;

    /**
     * Samba constructor.
     * Create an instance like this:
     * $api = Samba::forCustomer($sambaCustomerId).
     *
     * @param SambaConnector $connector
     * @param int $sambaCustomerId
     */
    public function __construct(SambaConnector $connector, int $sambaCustomerId)
    {
        $this->connector = $connector;
        $this->sambaCustomerId = $sambaCustomerId;
    }

    /**
     * Returns a single session by it's samba id.
     *
     * @param $id
     * @return array
     * @throws SambaConnectionException
     */
    public function getSession($id)
    {
        return $this->connector->get('/user_session/id/'.$id);
    }

    /**
     * Creates a new session.
     *
     * @param CreateSession $createSession
     * @return array
     * @throws SambaConnectionException
     */
    public function createSession(CreateSession $createSession)
    {
        $createSession->setSambaCustomerId($this->sambaCustomerId);
        // Samba wants us to make a put request when creating new objects
        return $this->connector->put('/user_session', $createSession->getData());
    }

    /**
     * Updates an existing session.
     *
     * @param UpdateSession $updateSession
     * @return array
     * @throws SambaConnectionException
     */
    public function updateSession(UpdateSession $updateSession)
    {
        $updateSession->setSambaCustomerId($this->sambaCustomerId);
        // Samba wants us to make a post request when updating objects
        return $this->connector->post('/user_session', $updateSession->getData());
    }

    /**
     * Deletes a session.
     *
     * @param int $sessionId
     * @return array
     * @throws SambaConnectionException
     */
    public function deleteSession($sessionId)
    {
        return $this->connector->delete('/session', ['id' => $sessionId]);
    }

    /**
     * Returns.
     *
     * @param Collection $sessionIds
     * @return Collection
     * @throws SambaConnectionException
     */
    public function getRecordings(Collection $sessionIds)
    {
        $allRecordings = $this->connector->get('/recordings');

        return collect($allRecordings)->filter(function ($recording) use ($sessionIds) {
            return $sessionIds->contains($recording['session_id']);
        })->map(function ($recording) {
            $recording['creation_date'] = Carbon::parse($recording['creation_date'], 'GMT')->toISOString();

            return $recording;
        });
    }

    /**
     * @param int $recordingId
     * @return array
     * @throws SambaConnectionException
     */
    public function getRecording($recordingId)
    {
        return $this->connector->get('/recording/id/'.$recordingId);
    }

    /**
     * @param int $recordingId
     * @return array
     * @throws SambaConnectionException
     */
    public function deleteRecording($recordingId)
    {
        $data = [
            'id' =>  $recordingId,
        ];

        return $this->connector->delete('/recording/id', $data);
    }

    /**
     * Lets a user join a session.
     *
     * @param JoinSession $joinSession
     * @return array
     * @throws SambaConnectionException
     */
    public function joinSession(JoinSession $joinSession)
    {
        return $this->connector->put('/user_invitee', $joinSession->getData());
    }

    /**
     * Update a users session participation data.
     *
     * @param UpdateSessionInvitee $updateSessionInvitee
     * @return array
     * @throws SambaConnectionException
     */
    public function updateSessionInvitee(UpdateSessionInvitee $updateSessionInvitee)
    {
        return $this->connector->post('/user_invitee', $updateSessionInvitee->getData());
    }

    /**
     * Removes session access.
     *
     * @param LeaveSession $leaveSession
     * @return array
     * @throws SambaConnectionException
     */
    public function leaveSession(LeaveSession $leaveSession)
    {
        return $this->connector->delete('/user_invitee', $leaveSession->getData());
    }

    /**
     * Use a specific token
     * We need this for some APIs because samba doesn't allow us to use them via the admin account.
     *
     * @param string $token
     * @return Samba
     */
    public function withAppSpecificAuth($token)
    {
        $this->connector->withAppSpecificAuth($token);

        return $this;
    }

    /**
     * Set up the authentication to use our default admin account.
     *
     * @return Samba
     */
    public function resetAuth()
    {
        $this->connector->resetAuth();

        return $this;
    }

    public function createAccount(CreateAccount $createAccount)
    {
        return $this->connector->put('/user', $createAccount->getData());
    }

    /**
     * Creates an instance of the Samba class for the specified samba customer id.
     *
     * @param $sambaCustomerId
     * @return Samba
     */
    public static function forCustomer($sambaCustomerId)
    {
        return app()->makeWith(self::class, ['sambaCustomerId' => $sambaCustomerId]);
    }
}
