<?php

use App\Models\MailTemplate;
use App\Models\MailTemplateTranslation;
use Illuminate\Database\Migrations\Migration;

class FixEntryCommentReportMailTemplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::transaction(function() {
            $mailTemplate = MailTemplate::where('app_id', 0)
                ->where('type', 'CommentNotDeleted')
                ->firstOrFail();

            $mailTemplateTranslation = MailTemplateTranslation::where('mail_template_id', $mailTemplate->id)
                ->where('language', 'de_formal')
                ->firstOrFail();
            $mailTemplateTranslation->title = 'Ihr Kommentar wurde überprüft';
            $mailTemplateTranslation->body = "Ein von Ihnen verfasster Kommentar wurde von einem Benutzer gemeldet und von einem Administrator als unbedenklich eingestuft.

Admin-Aktion:
%admin-action%

Begründung:
%admin-justification%

Kommentar (%comment-date%):
%comment%


Bitte antworten Sie nicht auf diese E-Mail, da es sich um eine vom System generierte E-Mail handelt.
Support: %contact-mail% | %contact-phone%";
            $mailTemplateTranslation->save();

            $mailTemplateTranslation = MailTemplateTranslation::where('mail_template_id', $mailTemplate->id)
                ->where('language', 'de')
                ->firstOrFail();
            $mailTemplateTranslation->title = 'Dein Kommentar wurde überprüft';
            $mailTemplateTranslation->body = "Ein von dir verfasster Kommentar wurde von einem Benutzer gemeldet und von einem Administrator als unbedenklich eingestuft.

Admin-Aktion:
%admin-action%

Begründung:
%admin-justification%

Kommentar (%comment-date%):
%comment%


Bitte antworte nicht auf diese E-Mail, da es sich um eine vom System generierte E-Mail handelt.
Support: %contact-mail% | %contact-phone%";
            $mailTemplateTranslation->save();


            $mailTemplateTranslation = MailTemplateTranslation::where('mail_template_id', $mailTemplate->id)
                ->where('language', 'en')
                ->firstOrFail();
            $mailTemplateTranslation->title = 'Your comment has been assessed';
            $mailTemplateTranslation->body = "Your comment has been reported and was reviewed by an administrator and assessed as being acceptable.

Admin action:
%admin-action%

Justification:
%admin-justification%

Comment (%comment-date%):
%comment%


Please do not reply to this system generated email.
Support: %contact-mail% | %contact-phone%";
            $mailTemplateTranslation->save();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // add mistakes back in lmao
        DB::transaction(function() {
            $mailTemplate = MailTemplate::where('app_id', 0)
                ->where('type', 'CommentNotDeleted')
                ->firstOrFail();

            $mailTemplateTranslation = MailTemplateTranslation::where('mail_template_id', $mailTemplate->id)
                ->where('language', 'de_formal')
                ->firstOrFail();
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

            $mailTemplateTranslation = MailTemplateTranslation::where('mail_template_id', $mailTemplate->id)
                ->where('language', 'de')
                ->firstOrFail();
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


            $mailTemplateTranslation = MailTemplateTranslation::where('mail_template_id', $mailTemplate->id)
                ->where('language', 'en')
                ->firstOrFail();
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
        });
    }
}
