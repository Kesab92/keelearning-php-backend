<?php

namespace App\Listeners;

use App\Mail\TestReminder;
use App\Models\EventHistory;
use Illuminate\Mail\Events\MessageSent;

class MailSentEventHistoryListener
{
    /**
     * Creates an event history entry when the mail is an test reminder mail and an user exists.
     * @param MessageSent $messageSent
     */
    public function handle(MessageSent $messageSent)
    {
        if (isset($messageSent->message->user)
            && isset($messageSent->message->testId)
            && isset($messageSent->message->mailClass)
            && $messageSent->message->user
            && $messageSent->message->testId
            && $messageSent->message->mailClass === TestReminder::class) {
            $historyEvent = new EventHistory();
            $historyEvent->user_id = $messageSent->message->user->id;
            $historyEvent->type = EventHistory::TEST_USER_NOTIFICATION;
            $historyEvent->foreign_id = $messageSent->message->testId;
            $historyEvent->email = $messageSent->message->user->email;
            $historyEvent->save();
        }
    }
}
