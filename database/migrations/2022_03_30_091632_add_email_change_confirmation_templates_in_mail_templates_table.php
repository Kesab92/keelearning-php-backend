<?php

use App\Models\MailTemplate;
use App\Models\MailTemplateTranslation;
use Illuminate\Database\Migrations\Migration;

class AddEmailChangeConfirmationTemplatesInMailTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $mailTemplate = new MailTemplate();
        $mailTemplate->app_id = 0;
        $mailTemplate->type = 'EmailChangeConfirmation';
        $mailTemplate->save();

        $mailTemplateTranslation = new MailTemplateTranslation();
        $mailTemplateTranslation->mail_template_id = $mailTemplate->id;
        $mailTemplateTranslation->language = 'de';
        $mailTemplateTranslation->title = "%appname% - E-Mail-Bestätigung";
        $mailTemplateTranslation->body = "Hallo %username%,
um deine E-Mail Adresse auf %new-email% zu aktualisieren, klick bitte auf folgenden Link:
%confirmation-link%

Bisherige E-Mail Adresse: %email%

Dein %appname% Team


Bitte antworte nicht auf diese E-Mail, da es sich um eine vom System generierte E-Mail handelt.
Support: %contact-mail% | %contact-phone%";
        $mailTemplateTranslation->save();

        $mailTemplateTranslation = new MailTemplateTranslation();
        $mailTemplateTranslation->mail_template_id = $mailTemplate->id;
        $mailTemplateTranslation->language = 'de_formal';
        $mailTemplateTranslation->title = "%appname% - E-Mail-Bestätigung";
        $mailTemplateTranslation->body = "Hallo %username%,
um Ihre E-Mail Adresse auf %new-email% zu aktualisieren, klicken Sie bitte auf folgenden Link:
%confirmation-link%

Bisherige E-Mail Adresse: %email%

Ihr %appname% Team


Bitte antworten Sie nicht auf diese E-Mail, da es sich um eine vom System generierte E-Mail handelt.
Support: %contact-mail% | %contact-phone%";
        $mailTemplateTranslation->save();

        $mailTemplateTranslation = new MailTemplateTranslation();
        $mailTemplateTranslation->mail_template_id = $mailTemplate->id;
        $mailTemplateTranslation->language = 'en';
        $mailTemplateTranslation->title = "%appname% - E-mail Confirmation";
        $mailTemplateTranslation->body = "Hello %username%,
To update your e-mail address to %new-email%, click the following link:
%confirmation-link%

Previous e-mail address: %email%

Your %appname% Team


Please do not reply to this system generated email.
Support: %contact-mail% | %contact-phone%

Terms and conditions of participation and use:
%tos%";
        $mailTemplateTranslation->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $mailTemplate = MailTemplate::where('type', 'EmailChangeConfirmation')->first();
        MailTemplateTranslation::where('mail_template_id', '=', $mailTemplate->id)->delete();
        $mailTemplate->delete();
    }
}
