<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class SMTPSettingsMail extends Mailable
{
    const MAIL_SUBJECT = 'Die SMTP-Einstellungen wurden erfolgreich konfiguriert.';

    public function build()
    {
        return $this->view('mail.check')
            ->subject(self::MAIL_SUBJECT);
    }
}
