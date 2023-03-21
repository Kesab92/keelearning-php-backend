<?php

use App\Models\MailTemplate;
use App\Models\MailTemplateTranslation;
use Illuminate\Database\Migrations\Migration;

class AddEntryCommentDeleteInMailTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->upForAuthor();
        $this->upForReporter();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $mailTemplateForReporter = MailTemplate::where('type', 'CommentDeletedForReporter')->first();
        MailTemplateTranslation::where('mail_template_id' ,'=', $mailTemplateForReporter->id)->delete();
        $mailTemplateForReporter->delete();

        $mailTemplateForAuthor = MailTemplate::where('type', 'CommentDeletedForAuthor')->first();
        MailTemplateTranslation::where('mail_template_id' ,'=', $mailTemplateForAuthor->id)->delete();
        $mailTemplateForAuthor->delete();
    }

    protected function upForReporter() {
        $mailTemplate = new MailTemplate();
        $mailTemplate->app_id = 0;
        $mailTemplate->type = 'CommentDeletedForReporter';
        $mailTemplate->save();

        $mailTemplateTranslation = new MailTemplateTranslation();
        $mailTemplateTranslation->mail_template_id = $mailTemplate->id;
        $mailTemplateTranslation->language='de';
        $mailTemplateTranslation->title = 'Der gemeldete Kommentar wurde von einem Admin für bedenklich befunden';
        $mailTemplateTranslation->body = "Admin-Aktion:
%admin-action%

Kommentar (%comment-date%):
%comment%


Bitte antworte nicht auf diese E-Mail, da es sich um eine vom System generierte E-Mail handelt.
Support: %contact-mail% | %contact-phone%";
        $mailTemplateTranslation->save();

        $mailTemplateTranslation = new MailTemplateTranslation();
        $mailTemplateTranslation->mail_template_id = $mailTemplate->id;
        $mailTemplateTranslation->language='de_formal';
        $mailTemplateTranslation->title = 'Der gemeldete Kommentar wurde von einem Admin für bedenklich befunden';
        $mailTemplateTranslation->body = "Admin-Aktion:
%admin-action%

Kommentar (%comment-date%):
%comment%


Bitte antworten Sie nicht auf diese E-Mail, da es sich um eine vom System generierte E-Mail handelt.
Support: %contact-mail% | %contact-phone%";
        $mailTemplateTranslation->save();

        $mailTemplateTranslation = new MailTemplateTranslation();
        $mailTemplateTranslation->mail_template_id = $mailTemplate->id;
        $mailTemplateTranslation->language='en';
        $mailTemplateTranslation->title = 'A reported comment was assessed as being problematic';
        $mailTemplateTranslation->body = "Admin action:
%admin-action%

Comment (%comment-date%):
%comment%


Please do not reply to this system generated email.
Support: %contact-mail% | %contact-phone%";
        $mailTemplateTranslation->save();
    }

    protected function upForAuthor() {
        $mailTemplate = new MailTemplate();
        $mailTemplate->app_id = 0;
        $mailTemplate->type = 'CommentDeletedForAuthor';
        $mailTemplate->save();

        $mailTemplateTranslation = new MailTemplateTranslation();
        $mailTemplateTranslation->mail_template_id = $mailTemplate->id;
        $mailTemplateTranslation->language='de';
        $mailTemplateTranslation->title = 'Der gemeldete Kommentar wurde von einem Admin für bedenklich befunden';
        $mailTemplateTranslation->body = "Admin-Aktion:
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
        $mailTemplateTranslation->language='de_formal';
        $mailTemplateTranslation->title = 'Der gemeldete Kommentar wurde von einem Admin für bedenklich befunden';
        $mailTemplateTranslation->body = "Admin-Aktion:
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
        $mailTemplateTranslation->title = 'A reported comment was assessed as being problematic';
        $mailTemplateTranslation->body = "Admin action:
%admin-action%

Justification:
%admin-justification%

Comment (%comment-date%):
%comment%


Please do not reply to this system generated email.
Support: %contact-mail% | %contact-phone%";
        $mailTemplateTranslation->save();
    }
}
