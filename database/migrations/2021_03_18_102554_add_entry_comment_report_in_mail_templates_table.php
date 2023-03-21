<?php

use App\Models\MailTemplate;
use App\Models\MailTemplateTranslation;
use Illuminate\Database\Migrations\Migration;

class AddEntryCommentReportInMailTemplatesTable extends Migration
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
        $mailTemplate->type = 'CommentNotDeleted';
        $mailTemplate->save();

        $mailTemplateTranslation = new MailTemplateTranslation();
        $mailTemplateTranslation->mail_template_id = $mailTemplate->id;
        $mailTemplateTranslation->language='de_formal';
        $mailTemplateTranslation->title = 'Ihr Kommentar wurde als bedenklich eingestuft';
        $mailTemplateTranslation->body = "Ein von Ihnen verfasster Kommentar wurde von einem Benutzer gemeldet und von einem Administrator als bedenklich eingestuft.

Admin-Aktion:
%admin-action%

Begründung:
%admin-justification%

Kommentar (%comment-date%):
%comment%


Bitte antworten Sie nicht auf diese E-Mail, da es sich um eine vom System generierte E-Mail handelt.
Support: %contact-mail% | %contact-phone%";
        $mailTemplateTranslation->save();

        $mailTemplateTranslation = new MailTemplateTranslation();
        $mailTemplateTranslation->mail_template_id = $mailTemplate->id;
        $mailTemplateTranslation->language='de';
        $mailTemplateTranslation->title = 'Dein Kommentar wurde als bedenklich eingestuft';
        $mailTemplateTranslation->body = "Ein von dir verfasster Kommentar wurde von einem Benutzer gemeldet und von einem Administrator als bedenklich eingestuft.

Admin-Aktion:
%admin-action%

Begründung:
%admin-justification%

Kommentar (%comment-date%):
%comment%


Bitte antworte nicht auf diese E-Mail, da es sich um eine vom System generierte E-Mail handelt.
Support: %contact-mail% | %contact-phone%";
        $mailTemplateTranslation->save();

        $mailTemplateTranslation = new MailTemplateTranslation();
        $mailTemplateTranslation->mail_template_id = $mailTemplate->id;
        $mailTemplateTranslation->language='en';
        $mailTemplateTranslation->title = 'Your comment has been assessed as being problematic';
        $mailTemplateTranslation->body = "Your comment has been reported and was reviewed by an administrator and assessed as being questionable.

Admin action:
%admin-action%

Justification:
%admin-justification%

Comment (%comment-date%):
%comment%


Please do not reply to this system generated email.
Support: %contact-mail% | %contact-phone%";
        $mailTemplateTranslation->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $mailTemplate = MailTemplate::where('type', 'CommentNotDeleted')->first();

        MailTemplateTranslation::where('mail_template_id' ,'=', $mailTemplate->id)->delete();
        $mailTemplate->delete();
    }
}
